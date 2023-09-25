<?php

namespace App\MessageHandler;

use App\Message\GeocoderMessage;
use App\Repository\EventRepository;
use App\Service\Geocoder;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class GeocoderHandler
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly Geocoder $geocoderService,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(GeocoderMessage $message): void
    {
        $event = $this->eventRepository->findOneBy(['id' => $message->getEventId()]);

        $address = $event->getLocation()->getAddress();

        $t = 1;
    }
}
