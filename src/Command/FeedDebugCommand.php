<?php

namespace App\Command;

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
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('url', InputArgument::OPTIONAL, 'Feed data url');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $url = $input->getArgument('url');
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $io->error('Url given is not a valid URL');

            return Command::FAILURE;
        }

        $req = $this->httpClient->request('GET', $url);
        $data = $req->getContent();

        foreach ($this->feedParser->parse($data) as $item) {
            // What should happen. Send item into queue system and in the next step map and validate data. But right
            // here for debugging we by-pass message system and try mapping the item.

            // @todo: Add feed configuration for dynamic mapping.
            $event = $this->feedMapper->getFeedItemFromArray($item);
            $io->writeln($event);
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
