<?php

namespace App\Command;

use App\Entity\Feed;
use App\Message\FeedItemDataMessage;
use App\Repository\FeedRepository;
use App\Service\Feeds\Mapper\FeedConfigurationMapper;
use App\Service\Feeds\Parser\FeedParserInterface;
use CuyZ\Valinor\Mapper\MappingError;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

#[AsCommand(
    name: 'app:feed:import',
    description: 'Parse feed and import events from it',
)]
final class FeedImportCommand extends Command
{
    private const DEFAULT_OPTION = -1;

    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly FeedParserInterface $feedParser,
        private readonly FeedConfigurationMapper $configurationMapper,
        private readonly FeedRepository $feedRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('feed-id', '', InputOption::VALUE_REQUIRED, 'Limit imports to the feed ID given', self::DEFAULT_OPTION)
            ->addOption('limit', '', InputOption::VALUE_REQUIRED, 'Limit the number of items parsed pr. feed', self::DEFAULT_OPTION);
    }

    /**
     * @throws MappingError
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $feedId = (int) $input->getOption('feed-id');
        $limit = (int) $input->getOption('limit');

        $feeds = $this->loadFeeds($io, $feedId);
        if (empty($feeds)) {
            return Command::FAILURE;
        }

        $progressBar = new ProgressBar($output);
        $progressBar->setFormat('Memory:%memory% [%bar%] Time:%elapsed%, Items:%current%');

        /** @var Feed $feed */
        foreach ($feeds as $feed) {
            if (!$feed->isEnabled()) {
                $io->error(sprintf('The feed "%s" is disabled', $feed->getName() ?? 'unknown'));

                return Command::FAILURE;
            }

            $index = 0;
            $config = $this->configurationMapper->getConfigurationFromArray($feed->getConfiguration());
            foreach ($this->feedParser->parse($feed, $config->url, $config->rootPointer) as $item) {
                $feedId = $feed->getId();
                if (!is_null($feedId)) {
                    $message = new FeedItemDataMessage($feedId, $config, $item);
                    try {
                        $this->messageBus->dispatch($message);
                    } catch (TransportException|\LogicException) {
                        // Ensure that message get into failed queue if connection to AMQP fails.
                        $this->messageBus->dispatch($message, [new TransportNamesStamp('failed')]);
                    }

                    $progressBar->advance();

                    ++$index;
                    if ($limit > 0 && $index >= $limit) {
                        break;
                    }
                }
            }

            $feed->setLastRead(new \DateTimeImmutable());
            $this->feedRepository->save($feed, true);
        }

        $progressBar->finish();
        $io->success('Feed(s) import completed.');

        return Command::SUCCESS;
    }

    /**
     * Helper function to load feed entities.
     *
     * @param symfonyStyle $io
     *   Symfony console output
     * @param int $feedId
     *   Given a feed id this will limit loading to that feed
     *
     * @return array
     *   Array of feed(s) entities
     */
    private function loadFeeds(SymfonyStyle $io, int $feedId = self::DEFAULT_OPTION): array
    {
        if (self::DEFAULT_OPTION !== $feedId) {
            $feed = $this->feedRepository->findOneBy(['id' => $feedId]);
            if (is_null($feed)) {
                $io->error(sprintf('Invalid feed id: %d', $feedId));

                return [];
            }

            return [$feed];
        }

        return $this->feedRepository->findBy(['enabled' => true]);
    }
}
