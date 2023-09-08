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

    public function get(FeedItemLocation $input, bool $update = true): LocationEntity
    {
        $address = $this->getAddress($input);
        $location = $this->getLocation($input);

        if (!is_null($location) && $address?->getLocations()->contains($location)) {
            // Location have been seen before, so reuse it (the fast track).
            // @todo: update.
            return $location;
        }

        if (is_null($address)) {
            // Create new address
            $address = new Address();
            $address->setCity($input->city)
                ->setCountry($input->country)
                ->setRegion($input->region)
                ->setPostalCode($input->postalCode)
                ->setStreet($input->street)
                ->setSuite($input->suite);
            $this->addressRepository->save($address, true);
        }

        if (is_null($location)) {
            // Create new location.
            $location = new LocationEntity();
            $location->setAddress($address)
                ->setImage($input->image)
                ->setUrl($input->url)
                ->setName($input->name)
                ->setMail($input->mail)
                ->setTelephone($input->telephone);

            $this->locationRepository->save($location, true);
        }

        // @todo: update.

        return $location;
    }

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
