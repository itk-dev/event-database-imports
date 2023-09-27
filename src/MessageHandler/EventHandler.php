<?php

namespace App\MessageHandler;

use App\Factory\EventFactory;
use App\Message\EventMessage;
use App\Message\ImageMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class EventHandler
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly EventFactory $eventFactory,
    ) {
    }

    public function __invoke(EventMessage $message): void
    {
        $item = $message->getItem();

        try {
            $entity = $this->eventFactory->createOrUpdate($item);
        } catch (\Exception $e) {
            // @todo: better message.
            throw new UnrecoverableMessageHandlingException($e->getMessage());
        }

        $id = $entity->getId();
        if (!is_null($id)) {
            $this->messageBus->dispatch(new ImageMessage($id, $entity->getImage()?->getId()));
        } else {
            throw new UnrecoverableMessageHandlingException('Event without id detected');
        }
    }
}
