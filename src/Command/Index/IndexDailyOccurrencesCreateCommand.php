<?php

namespace App\Command\Index;

use App\Exception\IndexingException;
use App\Service\Indexing\IndexingDailyOccurrences;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:index:daily-create',
    description: 'Create daily occurrences index if it doesnt exist',
)]
class IndexDailyOccurrencesCreateCommand extends Command
{
    public function __construct(
        private readonly IndexingDailyOccurrences $indexingService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            if (!$this->indexingService->indexExists()) {
                $this->indexingService->createIndex();

                $io->success('Index created');
            } else {
                $io->caution('Index exists. Aborting.');
            }
        } catch (IndexingException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
