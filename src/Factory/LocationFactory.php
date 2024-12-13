<?php

namespace App\Factory;

use App\Entity\Address;
use App\Entity\Location;
use App\Model\Feed\FeedItemLocation;
use App\Repository\AddressRepository;
use App\Repository\LocationRepository;
use App\Utils\UriHelper;
use Psr\Log\LoggerInterface;

final readonly class LocationFactory
{
    public function __construct(
        private LocationRepository $locationRepository,
        private AddressRepository $addressRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Create or update location entity.
     *
     * @param FeedItemLocation $input
     *   Location information from feed item
     *
     * @return Location
     *   Location entity base on feed data
     */
    public function createOrUpdate(FeedItemLocation $input, ?string $base = null): Location
    {
        $address = $this->getAddress($input);
        $location = $this->getLocation($input);

        $address ??= new Address();
        if (!is_null($input->city)) {
            $address->setCity($input->city);
        }
        if (!is_null($input->country)) {
            $address->setCountry($input->country);
        }
        if (!is_null($input->region)) {
            $address->setRegion($input->region);
        }
        if ($input->postalCode) {
            $address->setPostalCode($input->postalCode);
        }
        if (!is_null($input->street)) {
            $address->setStreet($input->street);
        }
        $coordinates = $input->coordinates;
        if (isset($coordinates->latitude, $coordinates->longitude)) {
            $address->setLatitude(floatval($coordinates->latitude));
            $address->setLongitude(floatval($coordinates->longitude));
        }
        $this->addressRepository->save($address);

        $location ??= new Location();
        $location->setAddress($address);
        if (!is_null($input->image)) {
            $location->setImage($input->image);
        }
        if (!is_null($input->url)) {
            try {
                $location->setUrl(UriHelper::getAbsoluteUrl($input->url, $base));
            } catch (\RuntimeException $e) {
                $this->logger->error('Location: '.$e->getMessage());
            }
        }
        if (!is_null($input->name)) {
            $location->setName($input->name);
        }
        if (!is_null($input->mail)) {
            $location->setMail($input->mail);
        }
        if (!is_null($input->telephone)) {
            $location->setTelephone($input->telephone);
        }
        $location->setDisabilityAccess($input->disabilityAccess);
        $this->locationRepository->save($location);

        return $location;
    }

    /**
     * Try to get address for at location base on feed data.
     *
     * @param FeedItemLocation $location
     *   Location information from feed
     *
     * @return Address|null
     *   Address entity if found else null
     */
    private function getAddress(FeedItemLocation $location): ?Address
    {
        $values = [];

        // @TODO Figure out if we can lookup by coordinates. Problem 1: precision, Problem 2: We also goecode the street address with DAWA coordinates, overriding the given coordinates.
        // Lookup base on coordinates
        // $latitude = $location->coordinates?->latitude;
        // $longitude = $location->coordinates?->longitude;
        // if (!is_null($longitude) && !is_null($latitude)) {
        //    $values = [
        //        'latitude' => floatval($latitude),
        //        'longitude' => floatval($longitude),
        //    ];
        // }

        // Lookup base on city, street (as it may not have been geolocation encoded yet).
        if (empty($values)) {
            $values = array_filter([
                'postalCode' => $location->postalCode,
                'street' => $location->street,
            ]);
        }

        return $this->addressRepository->findOneBy($values);
    }

    /**
     * Try to get location from database.
     *
     * @param FeedItemLocation $location
     *   Location information from feed
     *
     * @return Location|null
     *   Fund location entity or null
     */
    private function getLocation(FeedItemLocation $location): ?Location
    {
        $values = array_filter([
            'name' => $location->name,
            'mail' => $location->mail,
            'telephone' => $location->telephone,
        ]);

        return $this->locationRepository->findOneBy($values);
    }
}
