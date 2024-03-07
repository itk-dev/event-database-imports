<?php

namespace App\Command\Index;

use App\Model\Indexing\IndexNames;
use App\Service\Populate;
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
    name: 'app:index:populate',
    description: 'Populate (re-index) an index',
)]
final class IndexPopulateCommand extends Command
{
    public function __construct(
        private readonly Populate $populate,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'indexes',
                InputArgument::IS_ARRAY,
                'Indexes to create (separate multiple indexes with a space)',
                IndexNames::values(),
                function (CompletionInput $input): array {
                    return array_filter(IndexNames::values(), fn ($item) => str_starts_with($item, $input->getCompletionValue()));
                }
            )
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force execution ignoring locks')
            ->addOption('id', null, InputOption::VALUE_OPTIONAL, 'Single table record id (try to populate single record)', -1)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $inputIndexes = $input->getArgument('indexes');
        $id = (int) $input->getOption('id');
        $force = $input->getOption('force');

        foreach ($inputIndexes as $index) {
            if (!in_array($index, IndexNames::values())) {
                $io->error(sprintf('Index %s does not exist', $index));

                return Command::FAILURE;
            }

            $section = $output->section();
            $progressBar = new ProgressBar($section);
            $progressBar->setFormat('[%bar%] %elapsed% (%memory%) - %message%');
            $progressBar->start();
            $progressBar->setMessage(sprintf('Populating index %s …', $index));
            $progressBar->display();

            foreach ($this->populate->populate($index, $id, $force) as $message) {
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
