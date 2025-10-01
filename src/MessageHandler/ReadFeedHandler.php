<?php

namespace App\MessageHandler;

use App\Message\ReadFeedMessage;
use App\Repository\FeedRepository;
use App\Service\Feeds\Reader\FeedReader;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class ReadFeedHandler
{
    public function __construct(
        private FeedReader $feedReader,
        private FeedRepository $feedRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ReadFeedMessage $message): void
    {
        try {
            $feed = $this->feedRepository->findOneBy(['id' => $message->feedId]);

            if (null !== $feed) {
                $this->feedReader->readFeed($feed, $message->limit, $message->force);

                $this->logger->info('Feed read successfully', ['feed_id' => $message->feedId]);
            } else {
                $this->logger->error('Feed not found', ['feed_id' => $message->feedId]);
            }
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }
}
