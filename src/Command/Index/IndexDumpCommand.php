<?php

namespace App\Command\Index;

use App\Model\Indexing\IndexNames;
use App\Service\Dump;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:index:dump',
    description: 'Dump an index',
)]
final class IndexDumpCommand extends Command
{
    public function __construct(
        private readonly Dump $dump,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'indexes',
                InputArgument::IS_ARRAY,
                'Indexes to dump (separate multiple indexes with a space)',
                IndexNames::values(),
                function (CompletionInput $input): array {
                    return array_filter(IndexNames::values(), fn ($item) => str_starts_with($item, $input->getCompletionValue()));
                }
            )
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Path to write data to', './src/DataFixtures/indexes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $inputIndexes = $input->getArgument('indexes');
        $path = (string) $input->getOption('path');

        foreach ($inputIndexes as $index) {
            if (!in_array($index, IndexNames::values())) {
                $io->error(sprintf('Index %s does not exist', $index));

                return Command::FAILURE;
            }

            $progressBar = new ProgressBar($output);
            $progressBar->setFormat('[%bar%] %elapsed% (%memory%) - %message%');
            $progressBar->start();
            $progressBar->setMessage(sprintf('Dumping index %s …', $index));
            $progressBar->display();

            foreach ($this->dump->dump($index, $path) as $message) {
                $progressBar->setMessage($message);
                $progressBar->advance();
            }

            $progressBar->finish();
        }

        // Start the command line on a new line.
        $output->writeln('');

        return Command::SUCCESS;
    }
}
