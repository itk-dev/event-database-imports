<?php

namespace App\Command\Index;

use App\Exception\IndexingException;
use App\Model\Indexing\IndexNames;
use App\Service\Indexing\IndexingInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:index:create',
    description: 'Create index(es) if it/they doesnt exist',
)]
final class IndexCreateCommand extends Command
{
    public function __construct(
        private readonly iterable $indexingServices,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'indexes',
            InputArgument::IS_ARRAY,
            'Indexes to create (separate multiple indexes with a space)',
            IndexNames::values(),
            function (CompletionInput $input): array {
                return array_filter(IndexNames::values(), fn ($item) => str_starts_with($item, $input->getCompletionValue()));
            }
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $inputIndexes = $input->getArgument('indexes');

        /** @var IndexingInterface[] $indexingServices */
        $indexingServices = $this->indexingServices instanceof \Traversable ? iterator_to_array($this->indexingServices) : $this->indexingServices;

        foreach ($inputIndexes as $index) {
            if (!array_key_exists($index, $indexingServices)) {
                $io->error('Indexing service for index '.$index.' does not exist');
                continue;
            }
            $service = $indexingServices[$index];

            try {
                if (!$service->indexExists()) {
                    $service->createIndex();

                    $io->success('Index created: '.$index);
                } else {
                    $io->caution('Index exists ('.$index.'). Aborting.');
                }
            } catch (IndexingException $e) {
                $io->error($e->getMessage());

                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
