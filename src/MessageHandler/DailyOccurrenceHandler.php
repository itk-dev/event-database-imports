<?php

namespace App\MessageHandler;

use App\Factory\DailyOccurrencesFactory;
use App\Message\DailyOccurrenceMessage;
use App\Message\IndexMessage;
use App\Model\Indexing\IndexNames;
use App\Repository\EventRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class DailyOccurrenceHandler
{
    public function __construct(
        private DailyOccurrencesFactory $dailyOccurrencesFactory,
        private EventRepository         $eventRepository,
        private MessageBusInterface     $messageBus,
    ) {
    }

    public function __invoke(DailyOccurrenceMessage $message): void
    {
        $event = $this->eventRepository->findOneBy(['id' => $message->getEventId()]);
        if (is_null($event)) {
            throw new UnrecoverableMessageHandlingException(sprintf('Unable to load event %d in DailyOccurrence Handler', $message->getEventId()));
        }

        $this->dailyOccurrencesFactory->createOrUpdate($event);

        $this->messageBus->dispatch(new IndexMessage($message->getEventId(), IndexNames::Events));
    }
}
