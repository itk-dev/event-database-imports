<?php

namespace App\MessageHandler;

use App\Exception\GeocoderException;
use App\Message\DailyOccurrenceMessage;
use App\Message\GeocoderMessage;
use App\Message\IndexMessage;
use App\Repository\AddressRepository;
use App\Repository\EventRepository;
use App\Service\Geocoder;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class GeocoderHandler
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly Geocoder $geocoderService,
        private readonly AddressRepository $addressRepository,
        private readonly LoggerInterface $logger,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(GeocoderMessage $message): void
    {
        $event = $this->eventRepository->findOneBy(['id' => $message->getEventId()]);
        $address = $event?->getLocation()?->getAddress();
        if (!is_null($address)) {
            try {
                $coordinates = $this->geocoderService->encode($address);
                $address->setLatitude($coordinates[0]);
                $address->setLongitude($coordinates[0]);
                $this->addressRepository->save($address, true);
            } catch (GeocoderException|InvalidArgumentException $e) {
                // It is fin that no all addresses are possible to geo-encode, so we just log the database id for later
                // debugging.
                $this->logger->info(sprintf('Unable to geocode address: %d', $address->getId() ?? -1));
            }
        }

        // Send the event into the search index and at the same time to the occurrence splitter.
        $this->messageBus->dispatch(new DailyOccurrenceMessage($message->getEventId()));
        $this->messageBus->dispatch(new IndexMessage($message->getEventId()));
    }
}
