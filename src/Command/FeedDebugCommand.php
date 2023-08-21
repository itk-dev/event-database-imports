<?php

namespace App\Command;

use App\Repository\FeedRepository;
use App\Services\Feeds\Mapper\FeedMapperInterface;
use App\Services\Feeds\Parser\FeedParserInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * THIS COMMAND IS ONLY HERE DURING DEVELOPMENT FOR FASTER FEED PARSING TEST/DEVELOPMENT.
 */
#[AsCommand(
    name: 'app:feed:debug',
    description: 'Try parsing feed and output raw data',
)]
class FeedDebugCommand extends Command
{
    public function __construct(
        private readonly FeedParserInterface $feedParser,
        private readonly FeedMapperInterface $feedMapper,
        private readonly FeedRepository $feedRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('feedId', InputArgument::REQUIRED, 'Database feed id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $feedId = $input->getArgument('feedId');

        // @todo: Convert config array to value object.
        $feed = $this->feedRepository->findOneBy(['id' => $feedId]);
        $config = $feed->getConfiguration();

        $rootPointer = $config['rootPointer'] ?? '/-';
        foreach ($this->feedParser->parse($config['url'], $rootPointer) as $item) {
            // What should happen. Send item into queue system and in the next step map and validate data. But right
            // here for debugging we by-pass message system and try mapping the item.
            $event = $this->feedMapper->getFeedItemFromArray($item, $config['mapping'], $config['dateFormat']);
            $event->feedId = $feedId;
            $io->writeln($event);
        }

        $io->success('Feed debugging completed.');

        return Command::SUCCESS;
    }
}
