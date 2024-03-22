<?php

namespace App\MessageHandler;

use App\Message\FeedItemDataMessage;
use App\Message\FeedNormalizationMessage;
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
    ) {
    }

    public function __invoke(FeedItemDataMessage $message): void
    {
        try {
            $feedItem = $this->feedMapper->getFeedItemFromArray($message->getData(), $message->getConfiguration());
            $feedItem->feedId = $message->getFeedId();
        } catch (MappingError $e) {
            throw new UnrecoverableMessageHandlingException($e->getMessage(), $e->getCode(), $e);
        }

        $this->messageBus->dispatch(new FeedNormalizationMessage($message->getConfiguration(), $feedItem));
    }
}
