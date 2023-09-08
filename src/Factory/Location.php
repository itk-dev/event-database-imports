<?php

namespace App\Factory;

use App\Entity\Address;
use App\Entity\Location as LocationEntity;
use App\Model\Feed\FeedItemLocation;
use App\Repository\AddressRepository;
use App\Repository\LocationRepository;

class Location
{
    public function __construct(
        private readonly LocationRepository $locationRepository,
        private readonly AddressRepository $addressRepository
    ) {
    }

    /**
     * Create or update location entity.
     *
     * @param feedItemLocation $input
     *   Location information from feed item
     *
     * @return locationEntity
     *   Location entity base on feed data
     */
    public function createOrUpdate(FeedItemLocation $input): LocationEntity
    {
        $address = $this->getAddress($input);
        $location = $this->getLocation($input);

        $address = $address ?? new Address();
        $input->city ?? $address->setCity($input->city);
        $input->country ?? $address->setCountry($input->country);
        $input->region ?? $address->setRegion($input->region);
        $input->postalCode ?? $address->setPostalCode($input->postalCode);
        $input->street ?? $address->setStreet($input->street);
        $input->suite ?? $address->setSuite($input->suite);
        $this->addressRepository->save($address, true);

        $location = $location ?? new LocationEntity();
        $location->setAddress($address);
        $input->image ?? $location->setImage($input->image);
        $input->url ?? $location->setUrl($input->url);
        $input->name ?? $location->setName($input->name);
        $input->mail ?? $location->setMail($input->mail);
        $input->telephone ?? $location->setTelephone($input->telephone);
        $this->locationRepository->save($location, true);

        return $location;
    }

    /**
     * Try to get address for at location base on feed data.
     *
     * @param feedItemLocation $location
     *   Location information from feed
     *
     * @return Address|null
     *   Address entity if found else null
     */
    private function getAddress(FeedItemLocation $location): ?Address
    {
        $values = [];

        // Lookup base on coordinates
        $latitude = $location->coordinates?->latitude;
        $longitude = $location->coordinates?->longitude;
        if (!is_null($longitude) && !is_null($latitude)) {
            $values = [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];
        }

        // Lookup base on city, street (as it may not have been geolocation encoded yet).
        if (empty($values)) {
            $values = array_filter([
                'city' => $location->city,
                'street' => $location->street,
                'suite' => $location->suite,
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
     * @return LocationEntity|null
     *   Fund location entity or null
     */
    private function getLocation(FeedItemLocation $location): ?LocationEntity
    {
        $values = array_filter([
            'name' => $location->name,
            'mail' => $location->mail,
            'telephone' => $location->telephone,
        ]);

        return $this->locationRepository->findOneBy($values);
    }
}
