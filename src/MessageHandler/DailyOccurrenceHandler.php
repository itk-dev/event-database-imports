<?php

namespace App\MessageHandler;

use App\Factory\DailyOccurrencesFactory;
use App\Message\DailyOccurrenceMessage;
use App\Repository\EventRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class DailyOccurrenceHandler
{
    public function __construct(
        private readonly DailyOccurrencesFactory $dailyOccurrencesFactory,
        private readonly EventRepository $eventRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(DailyOccurrenceMessage $message): void
    {
        $event = $this->eventRepository->findOneBy(['id' => $message->getEventId()]);
        if (is_null($event)) {
            throw new UnrecoverableMessageHandlingException(sprintf('Unable to load event %d in DailyOccurrence Handler', $message->getEventId()));
        }

        $this->dailyOccurrencesFactory->createOrUpdate($event);

        throw new UnrecoverableMessageHandlingException('Not implemented yet');
    }
}
