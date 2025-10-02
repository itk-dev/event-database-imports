<?php

namespace App\Service\Feeds\Reader;

use App\Entity\Feed;
use App\Message\FeedItemDataMessage;
use App\Message\ReadFeedMessage;
use App\Repository\FeedItemRepository;
use App\Repository\FeedRepository;
use App\Service\Feeds\Mapper\FeedConfigurationMapper;
use App\Service\Feeds\Parser\FeedParserInterface;
use CuyZ\Valinor\Mapper\MappingError;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class FeedReader implements FeedReaderInterface
{
    public const string SYNC_QUEUE = 'sync';
    public const string ASYNC_QUEUE = 'async';

    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly FeedParserInterface $feedParser,
        private readonly FeedConfigurationMapper $configurationMapper,
        private readonly FeedRepository $feedRepository,
        private readonly FeedItemRepository $feedItemRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Get enabled feed entities.
     *
     * @return array<Feed>
     */
    public function getEnabledFeeds(int $limit, bool $force = false, array $feedIds = []): array
    {
        if (0 === count($feedIds)) {
            $feeds = $this->feedRepository->findBy(['enabled' => true]);
        } else {
            $feeds = $this->feedRepository->findBy(['id' => $feedIds, 'enabled' => true]);
        }

        return $feeds;
    }

    /**
     * Load enabled feed entities.
     *
     * @throws MappingError
     */
    public function readFeeds(int $limit = FeedReaderInterface::DEFAULT_OPTION, bool $force = false, array $feedIds = []): iterable
    {
        $feeds = $this->getEnabledFeeds($limit, $force, $feedIds);

        foreach ($feeds as $feed) {
            foreach ($this->readFeed($feed, $limit, $force) as $item) {
                yield $item;
            }
        }
    }

    public function readFeedsASync(int $limit = FeedReaderInterface::DEFAULT_OPTION, bool $force = false, array $feedIds = []): iterable
    {
        $feeds = $this->getEnabledFeeds($limit, $force, $feedIds);

        foreach ($feeds as $feed) {
            $message = new ReadFeedMessage($feed->getId(), $limit, $force);

            $this->messageBus->dispatch($message, [new TransportNamesStamp(self::ASYNC_QUEUE)]);

            yield $feed;
        }
    }

    /**
     * Read feed.
     *
     * @throws MappingError
     */
    public function readFeed(Feed $feed, int $limit, bool $force = false): iterable
    {
        if (!$feed->isEnabled()) {
            throw new \RuntimeException(sprintf('Feed: %s (%d) is not enabled', $feed->getName() ?? 'Unknown', $feed->getId() ?? -1));
        }

        try {
            $start = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

            // To do cleanup we need to handle FeedItemDataMessages synchronously because cleanup
            // is based on 'lastSeen' timestamp. So we need to have looped over the whole feed
            // before doing cleanup.
            $transportName = $feed->isSyncToFeed() ? 'sync' : 'async';

            $index = 0;
            $config = $this->configurationMapper->getConfigurationFromArray($feed->getConfiguration());
            foreach ($this->feedParser->parse($feed, $config->url, $config->rootPointer) as $item) {
                $feedId = $feed->getId();
                if (!is_null($feedId)) {
                    $message = new FeedItemDataMessage($feedId, $config, $item, $force);
                    try {
                        $this->messageBus->dispatch($message, [new TransportNamesStamp($transportName)]);
                    } catch (TransportException|\LogicException) {
                        // Ensure that message get into failed queue if connection to AMQP fails.
                        $this->messageBus->dispatch($message, [new TransportNamesStamp('failed')]);
                    }

                    ++$index;
                    if ($this->isLimitReached($index, $limit)) {
                        break;
                    }
                }

                yield;
            }

            // We can only do cleanup if we run without limit
            if (FeedReaderInterface::DEFAULT_OPTION === $limit) {
                $this->cleanUp($feed, $start);
            }

            $feed->setLastRead(new \DateTimeImmutable());
            $feed->setLastReadCount($index);
            $feed->setMessage(null);
            $this->feedRepository->save($feed, true);
        } catch (\Exception $exception) {
            $feed->setMessage($exception->getMessage());
            $feed->setLastReadCount(null);
            $this->feedRepository->save($feed, true);

            $this->logger->error($exception->getMessage());

            throw $exception;
        }
    }

    private function isLimitReached(int $index, int $limit): bool
    {
        if (FeedReaderInterface::DEFAULT_OPTION === $limit) {
            // No limit set
            return false;
        }

        return $index >= $limit;
    }

    /**
     * Clean up FeedItems/Events. Remove all FeedItems where
     *  - item was not seen on the last run and event is null.
     *  - item was not seen on the last run and "sync to feed" is active for feed.
     */
    private function cleanUp(Feed $feed, \DateTimeInterface $date): void
    {
        foreach ($this->feedItemRepository->findByByLastSeen($feed, $date) as $feedItem) {
            if (null === $feedItem->getEvent() || $feed->isSyncToFeed()) {
                $this->feedItemRepository->remove($feedItem);
            }
        }
    }
}
