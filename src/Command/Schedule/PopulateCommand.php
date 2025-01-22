<?php

namespace App\Command\Schedule;

use App\Model\Indexing\IndexNames;
use App\Service\IndexManager;
use App\Service\Populate;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Scheduler\Attribute\AsCronTask;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Command to import feeds and populate the index.
 *
 * This command is designed to be run with Symfony's scheduler component.
 * Because of this it has no output because some output options are
 * incompatible with running in a scheduled worker.
 */
#[AsCommand(
    name: 'app:schedule:populate',
    description: 'Populate index by schedule',
)]
#[AsCronTask(expression: '30 * * * *', schedule: 'default')]
class PopulateCommand extends Command
{
    use LockableTrait;
    use MonitorTrait;

    public function __construct(
        private readonly Populate $populate,
        private readonly LoggerInterface $logger,
        private readonly HttpClientInterface $client,
        private readonly IndexManager $indexManager,
        private readonly string $monitoringUrl,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');
            $this->logger->warning('Scheduled index populate: Cannot acquire lock');

            return Command::SUCCESS;
        }

        try {
            $progressBar = new ProgressBar($output);
            $progressBar->setFormat('[%bar%] %elapsed% (%memory%) - %message%');
            $progressBar->start();

            foreach (IndexNames::values() as $index) {
                $progressBar->setMessage(sprintf('Populating index %s …', $index));
                $progressBar->display();

                foreach ($this->populate->populate($index) as $message) {
                    $progressBar->setMessage($message);
                    $progressBar->advance();
                }
            }

            $progressBar->finish();

            if ('' !== $this->monitoringUrl) {
                try {
                    $this->client->request('GET', $this->monitoringUrl);

                    $this->logger->info('Scheduled index populate: Successfully called monitoringUrl');
                } catch (\Throwable $e) {
                    $this->logger->error('Scheduled index populate: Error calling monitoringUrl: '.$e->getMessage());
                }
            }

            $this->logger->info('Scheduled index populate: Populated indexes successfully');

            // Cleanup non aliased indices that may not have been removed.
            $deletedCount = $this->indexManager->deleteNonAliased();
            if ($deletedCount > 0) {
                $this->logger->info('Scheduled index populate: Deleted '.$deletedCount.' non alias indices successfully');
            }

            $this->ping('app:schedule:populate', $this->monitoringUrl, $this->client, $this->logger);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            $this->logger->error('Scheduled index populate: Populate indexes error: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
