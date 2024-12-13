<?php

namespace App\Command\Feed;

use App\Entity\Feed;
use App\Service\Feeds\Reader\FeedReaderInterface;
use CuyZ\Valinor\Mapper\MappingError;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:feed:import',
    description: 'Parse feed and import events from it',
)]
final class FeedImportCommand extends Command
{
    public function __construct(
        private readonly FeedReaderInterface $feedReader,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('feed-id', '', InputOption::VALUE_REQUIRED, 'Limit imports to the feed ID given', FeedReaderInterface::DEFAULT_OPTION)
            ->addOption('limit', '', InputOption::VALUE_REQUIRED, 'Limit the number of items parsed pr. feed', FeedReaderInterface::DEFAULT_OPTION)
            ->addOption('force', '', InputOption::VALUE_NONE, 'Force update from feed ignoring hash');
    }

    /**
     * @throws MappingError
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$output instanceof ConsoleOutputInterface) {
            // ConsoleOutputInterface is required for Sections & Table output
            throw new \RuntimeException('Command not running in console');
        }

        $io = new SymfonyStyle($input, $output);

        $feedId = (int) $input->getOption('feed-id');
        $limit = (int) $input->getOption('limit');
        $force = $input->getOption('force');

        $feedIds = FeedReaderInterface::DEFAULT_OPTION === $feedId ? [] : [$feedId];

        $feeds = $this->feedReader->getEnabledFeeds($limit, $force, $feedIds);

        $table = $this->initializeTable($output);
        $progressBar = $this->initializeProgressbar($output);

        $count = count($feeds);
        $pointer = 0;
        $totalTime = 0.0;

        foreach ($feeds as $feed) {
            $index = 0;
            $start = \hrtime(true);
            try {
                $progressBar->setMessage(sprintf('%d/%d Importing feed %s …', ++$pointer, $count, $feed));

                foreach ($this->feedReader->readFeed($feed, $limit, $force) as $i) {
                    $progressBar->advance();
                    ++$index;
                }

                $end = \hrtime(true);
                $time = ($end - $start) / 1000000000;
                $totalTime += $time;

                $this->appendTableRow($table, $feed, $index, $time, 'Success');
            } catch (\Exception $e) {
                $time = (\hrtime(true) - $start) / 1000000000;
                $this->appendTableRow($table, $feed, $index, $time, $e->getMessage());
            }
        }

        $progressBar->finish();
        $this->appendTableFooter($table, $progressBar, $totalTime);
        $io->success('Feed(s) import completed.');

        return Command::SUCCESS;
    }

    private function initializeProgressbar(ConsoleOutputInterface $output): ProgressBar
    {
        $progressSection = $output->section();

        $progressBar = new ProgressBar($progressSection);
        $progressBar->setFormat('Memory:%memory% [%bar%] Time:%elapsed%, Items:%current% - %message%');

        return $progressBar;
    }

    private function initializeTable(ConsoleOutputInterface $output): Table
    {
        $tableSection = $output->section();

        $table = new Table($tableSection);
        $table->setHeaders(['ID', 'Feed', '#imported', 'Time', 'Status']);
        $table->setColumnWidths([2, 20, 9, 5, 28]);
        $table->setColumnMaxWidth(0, 2);
        $table->setColumnMaxWidth(1, 20);
        $table->setColumnMaxWidth(2, 9);
        $table->setColumnMaxWidth(3, 5);
        $table->setColumnMaxWidth(4, 28);
        $table->render();

        return $table;
    }

    private function appendTableRow(Table $table, Feed $feed, int $index, float $time, string $status): void
    {
        $table->appendRow([
            new TableCell((string) $feed->getId(), ['style' => new TableCellStyle(['align' => 'right'])]),
            $feed->getName(),
            new TableCell((string) $index, ['style' => new TableCellStyle(['align' => 'right'])]),
            new TableCell(number_format($time, 2), ['style' => new TableCellStyle(['align' => 'right'])]),
            $status,
        ]);
    }

    private function appendTableFooter(Table $table, ProgressBar $progressBar, float $totalTime): void
    {
        $table->appendRow(new TableSeparator());
        $table->appendRow([
            '',
            '',
            new TableCell((string) $progressBar->getProgress(), ['style' => new TableCellStyle(['align' => 'right'])]),
            new TableCell(number_format($totalTime, 2), ['style' => new TableCellStyle(['align' => 'right'])]),
            '',
        ]);
    }
}
