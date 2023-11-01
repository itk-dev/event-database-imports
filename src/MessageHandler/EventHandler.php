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
            if ($this->eventFactory->isUpdatableOrNew($item)) {
                $entity = $this->eventFactory->createOrUpdate($item);
                $id = $entity->getId();
                if (!is_null($id)) {
                    $this->messageBus->dispatch(new ImageMessage($id, $entity->getImage()?->getId()));
                } else {
                    throw new UnrecoverableMessageHandlingException('Event without id detected');
                }
            }
        } catch (\Exception $e) {
            throw new UnrecoverableMessageHandlingException($e->getMessage());
        }
    }
}
