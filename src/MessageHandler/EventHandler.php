<?php

namespace App\MessageHandler;

use App\Message\EventMessage;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class EventHandler
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly EventRepository $eventRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(EventMessage $message): void
    {
        // Calculate hash used later for 2x.

        // Check for create or update.

        // Save data to the database.

        throw new UnrecoverableMessageHandlingException('Not implemented yet');
    }
}
