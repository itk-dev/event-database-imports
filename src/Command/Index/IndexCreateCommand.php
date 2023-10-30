<?php

namespace App\Command\Index;

use App\Exception\IndexingException;
use App\Service\Indexing\IndexingDailyOccurrences;
use App\Service\Indexing\IndexingEvents;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:index:create',
    description: 'Create daily occurrences index if it doesnt exist',
)]
class IndexCreateCommand extends Command
{
    private array $indexes = [
        'events' => 'indexingEvents',
        'daily' => 'indexingDailyOccurrences',
    ];

    public function __construct(
        private readonly IndexingDailyOccurrences $indexingDailyOccurrences,
        private readonly IndexingEvents $indexingEvents,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'indexes',
            InputArgument::IS_ARRAY,
            'Indexes to index (separate multiple indexes with a space)',
            array_keys($this->indexes),
            function (CompletionInput $input): array {
                $indexes = array_keys($this->indexes);
                return array_filter($indexes, fn($item) => str_starts_with($item, $input->getCompletionValue()));
            }
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $inputIndexes = $input->getArgument('indexes');

        foreach ($inputIndexes as $index) {
            try {
                $service = $this->{$this->indexes[$index]};
            if (!$service->indexExists()) {
                $service->createIndex();

                $io->success('Index created');
            } else {
                $io->caution('Index exists. Aborting.');
            }
        } catch (IndexingException $e) {
                $io->error($e->getMessage());

                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
