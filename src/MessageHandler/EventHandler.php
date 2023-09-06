<?php

namespace App\MessageHandler;

use App\Factory\Event as EventFactory;
use App\Message\EventMessage;
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

        // Check for create or update.
        $entity = null;
        try {
            $entity = $this->eventFactory->create($item);
        } catch (\Exception $e) {
            // @todo: better message.
            throw new UnrecoverableMessageHandlingException($e->getMessage());
        }

        // @todo: create next message
        throw new UnrecoverableMessageHandlingException('Not implemented yet');
    }


}
