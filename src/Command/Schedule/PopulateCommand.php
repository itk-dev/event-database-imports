<?php

namespace App\Command\Schedule;

use App\Model\Indexing\IndexNames;
use App\Service\Populate;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
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
    public function __construct(
        private readonly Populate $populate,
        private readonly LoggerInterface $logger,
        private readonly HttpClientInterface $client,
        private readonly string $monitoringUrl,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $progressBar = new ProgressBar($output);
        $progressBar->setFormat('[%bar%] %elapsed% (%memory%) - %message%');
        $progressBar->start();

        try {
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
                } catch (\Throwable $e) {
                    $this->logger->error('Error calling monitoringUrl: '.$e->getMessage());
                }
            }

            $this->logger->info('Populated indexes successfully');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            $this->logger->error('Populate indexes error: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
