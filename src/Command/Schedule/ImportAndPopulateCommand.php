<?php

namespace App\Command\Schedule;

use App\Model\Indexing\IndexNames;
use App\Service\Feeds\Reader\FeedReader;
use App\Service\Populate;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCommand(
    name: 'app:schedule:import-and-populate',
    description: 'Import feeds and populate index',
)]
#[AsCronTask(expression: '25 * * * *', schedule: 'default')]
class ImportAndPopulateCommand extends Command
{
    public function __construct(
        private readonly FeedReader $feedReader,
        private readonly Populate $populate,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->feedReader->readFeeds();

            foreach (IndexNames::values() as $indexName) {
                $this->populate->populate($indexName);
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return Command::FAILURE;
        }
    }
}
