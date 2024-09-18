<?php

namespace App\MessageHandler;

use App\Entity\FeedItem;
use App\Message\FeedItemDataMessage;
use App\Message\FeedItemNormalizationMessage;
use App\Repository\FeedItemRepository;
use App\Repository\FeedRepository;
use App\Service\Feeds\Mapper\FeedMapperInterface;
use CuyZ\Valinor\Mapper\MappingError;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class FeedItemDataHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private FeedMapperInterface $feedMapper,
        private FeedRepository $feedRepository,
        private FeedItemRepository $feedItemRepository,
    ) {
    }

    public function __invoke(FeedItemDataMessage $message): void
    {
        try {
            $feed = $this->feedRepository->find($message->getFeedId());
            if (null === $feed) {
                throw new \RuntimeException(sprintf('Feed with id (%d) does not exist', $message->getFeedId()));
            }

            $feedItemData = $this->feedMapper->getFeedItemFromArray($message->getData(), $message->getConfiguration());
            $feedItemData->feedId = $message->getFeedId();

            $feedItemEntity = $this->feedItemRepository->findOneBy(['feed' => $feed, 'feedItemId' => $feedItemData->id]);

            $oldHash = $feedItemEntity?->getHash();

            if (null === $feedItemEntity) {
                $feedItemEntity = new FeedItem($feed, $feedItemData->id, $message->getData());
            }

            $feedItemEntity->setLastSeenAt();
            $this->feedItemRepository->save($feedItemEntity, true);

            if ($message->isForceUpdate() || $oldHash !== $feedItemEntity->getHash()) {
                $this->messageBus->dispatch(new FeedItemNormalizationMessage($message->getConfiguration(), $feedItemData));
            }
        } catch (MappingError $e) {
            throw new UnrecoverableMessageHandlingException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
