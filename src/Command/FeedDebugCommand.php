<?php

namespace App\Command;

use App\Repository\FeedRepository;
use App\Services\Feeds\FeedParserInterface;
use App\Services\Mapper\FeedMapperInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:feed:debug',
    description: 'Try parsing feed and output raw data',
)]
class FeedDebugCommand extends Command
{
    public function __construct(
        private readonly FeedParserInterface $feedParser,
        private readonly FeedMapperInterface $feedMapper,
        private readonly HttpClientInterface $httpClient,
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

        $req = $this->httpClient->request('GET', $config['url']);
        $data = $req->getContent();

        $rootPointer = $config['rootPointer'] ?? '/-';
        foreach ($this->feedParser->parse($data, $rootPointer) as $item) {
            // What should happen. Send item into queue system and in the next step map and validate data. But right
            // here for debugging we by-pass message system and try mapping the item.

            // @todo: Add feed configuration for dynamic mapping.
            $event = $this->feedMapper->getFeedItemFromArray($item, $config['mapping'], $config['dateFormat']);
            $event->feedId = $feedId;
            $io->writeln($event);
        }

        $io->success('Feed debugging completed.');

        return Command::SUCCESS;
    }
}
