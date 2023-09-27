<?php

namespace App\MessageHandler;

use App\Exception\GeocoderException;
use App\Message\GeocoderMessage;
use App\Repository\AddressRepository;
use App\Repository\EventRepository;
use App\Service\Geocoder;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsMessageHandler]
final class GeocoderHandler
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly Geocoder $geocoderService,
        private readonly AddressRepository $addressRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @throws GeocoderException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function __invoke(GeocoderMessage $message): void
    {
        if (!is_null($message->getEventId())) {
            $event = $this->eventRepository->findOneBy(['id' => $message->getEventId()]);
        } else {
            throw new UnrecoverableMessageHandlingException('Missing event id in geo-coder handler');
        }

        $address = $event?->getLocation()?->getAddress();
        if (!is_null($address)) {
            $coordinates = $this->geocoderService->encode($address);
            $address->setLatitude($coordinates[0]);
            $address->setLongitude($coordinates[0]);
            $this->addressRepository->save($address, true);
        }

        // @TODO: send index massage and daily occurrences splitter message.
    }
}
