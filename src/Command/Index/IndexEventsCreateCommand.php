<?php

namespace App\Command\Index;

use App\Exception\IndexingException;
use App\Service\Indexing\IndexingEvents;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:index:event-create',
    description: 'Create event index if it doesnt exist',
)]
class IndexEventsCreateCommand extends Command
{
    /**
     * @param IndexingEvents $indexingService
     */
    public function __construct(
        private readonly IndexingEvents $indexingService
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
