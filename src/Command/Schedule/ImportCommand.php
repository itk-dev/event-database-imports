<?php

namespace App\Command\Schedule;

use App\Service\Feeds\Reader\FeedReader;
use App\Service\Populate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

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
    public function __construct(
        private readonly FeedReader $feedReader,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->feedReader->readFeeds();

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
