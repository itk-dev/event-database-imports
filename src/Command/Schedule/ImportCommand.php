<?php

namespace App\Command\Schedule;

use App\Service\Feeds\Reader\FeedReader;
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
    name: 'app:schedule:import',
    description: 'Import feeds by schedule',
)]
#[AsCronTask(expression: '15 * * * *', schedule: 'default')]
class ImportCommand extends Command
{
    use LockableTrait;
    use MonitorTrait;

    public function __construct(
        private readonly FeedReader $feedReader,
        private readonly LoggerInterface $logger,
        private readonly HttpClientInterface $client,
        private readonly string $monitoringUrl,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');
            $this->logger->warning('Schedule feeds import: Cannot acquire lock');

            return Command::SUCCESS;
        }

        try {
            $progressBar = new ProgressBar($output);
            $progressBar->setFormat('[%bar%] %elapsed% (%memory%): Imported %current% events');
            $progressBar->start();

            foreach ($this->feedReader->readFeedsASync() as $item) {
                $progressBar->advance();
            }

            $progressBar->finish();

            $this->logger->info('Schedule feeds import: Successfully imported feeds');

            $this->ping('app:schedule:import', $this->monitoringUrl, $this->client, $this->logger);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            $this->logger->error('Schedule feeds import: Error importing feeds: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
