<?php

namespace App\Command\Index;

use App\Exception\IndexingException;
use App\Service\Indexing\IndexingDailyOccurrences;
use App\Service\Indexing\IndexingEvents;
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
class PopulateIndexCommand extends Command
{
    private array $indexes = [
        'events',
        'daily',
    ];

    public function __construct(
        private readonly Populate $populate,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'index',
            InputArgument::REQUIRED,
            sprintf('Index to populate (one off %s)', implode(', ', $this->indexes)),
            null,
            function (CompletionInput $input): array {
                return array_filter($this->indexes, fn($item) => str_starts_with($item, $input->getCompletionValue()));
            }
        )
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force execution ignoring locks')
            ->addOption('id', null, InputOption::VALUE_OPTIONAL, 'Single table record id (try populate single record)', -1);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $index = $input->getArgument('index');
        $id = (int) $input->getOption('id');
        $force = $input->getOption('force');


        if (!in_array($index, $this->indexes)) {
            $io->error('Index service for index ('.$index.') do not exists');

            return Command::FAILURE;
        }

        $progressBar = new ProgressBar($output);
        $progressBar->setFormat('[%bar%] %elapsed% (%memory%) - %message%');
        $progressBar->start();

        foreach ($this->populate->populate($index, $id, $force) as $message) {
            $progressBar->setMessage($message);
            $progressBar->advance();
        }

        $progressBar->finish();

        // Start the command line on a new line.
        $output->writeln('');

        return Command::SUCCESS;
    }
}
