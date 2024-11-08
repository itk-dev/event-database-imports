<?php

namespace App\Command\Schedule;

use App\Model\Indexing\IndexNames;
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
    name: 'app:schedule:populate',
    description: 'Populate index by schedule',
)]
#[AsCronTask(expression: '30 * * * *', schedule: 'default')]
class PopulateCommand extends Command
{
    public function __construct(
        private readonly Populate $populate,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            foreach (IndexNames::values() as $index) {
                foreach ($this->populate->populate($index) as $message) {
                    // Do nothing
                }
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}