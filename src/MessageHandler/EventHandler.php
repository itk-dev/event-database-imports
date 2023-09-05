<?php

namespace App\MessageHandler;

use App\Message\EventMessage;
use App\Model\Feed\FeedItem;
use App\Repository\EventRepository;
use App\Repository\FeedRepository;
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
        private readonly FeedRepository $feedRepository,
    ) {
    }

    public function __invoke(EventMessage $message): void
    {
        $item = $message->getItem();
        $hash = $this->calculateHash($item);

        // Check for create or update.
        $feed = $this->feedRepository->find(['id' => $item->feedId]);
        $entity = $this->eventRepository->updateOrCreate($hash, $feed, $item);

        // Save data to the database.

        throw new UnrecoverableMessageHandlingException('Not implemented yet');
    }

    private function calculateHash(FeedItem $item): string
    {
        return hash('sha256', serialize($item));
    }
}
