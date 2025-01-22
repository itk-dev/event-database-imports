<?php

namespace App\Command\Index;

use App\Service\IndexManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:index:purge',
    description: 'Delete all indices that are not aliased',
)]
class IndexPurgeCommand extends Command
{
    public function __construct(private readonly IndexManager $indexManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $deletedCount = $this->indexManager->deleteNonAliased();

        $io->success('Deleted '.$deletedCount.' that were not aliased.');

        return Command::SUCCESS;
    }
}
