<?php

namespace App\Command\Index;

use App\Service\IndexManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:index:list',
    description: 'Lists all indices in the Elasticsearch cluster',
)]
class IndexListCommand extends Command
{
    public function __construct(private readonly IndexManager $indexManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            // Fetch all indices
            $indicesData = $this->indexManager->getAll();

            // Check if indices data exists
            if (empty($indicesData)) {
                $io->warning('No indices found in the Elasticsearch cluster.');

                return Command::SUCCESS;
            }

            // Prepare table data
            $tableData = [];
            $count = 0;
            foreach ($indicesData as $index) {
                $indexName = $index['index'] ?? 'unknown';
                $tableData[$indexName] = [
                    '#' => ++$count,
                    'Index' => $indexName,
                    'Aliases' => !empty($index['aliases']) ? implode(', ', $index['aliases']) : '',
                    'Docs Count' => $index['docs.count'] ?? 'N/A',
                    'Status' => $index['status'] ?? 'unknown',
                ];
            }
            ksort($tableData);

            // Display table
            $io->table(['#', 'Index', 'Aliases', 'Docs Count', 'Status'], $tableData);
            $io->success('All indices and aliases retrieved successfully.');
        } catch (\Exception $e) {
            $io->error('Error retrieving indices: '.$e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
