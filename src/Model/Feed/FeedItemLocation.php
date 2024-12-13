<?php

namespace App\Model\Feed;

final class FeedItemLocation
{
    public function __construct(
        public ?string $city = null,
        public ?string $country = null,
        public ?string $postalCode = null,
        public ?string $street = null,
        public ?string $suite = null,
        public ?string $name = null,
        public ?string $mail = null,
        public ?string $telephone = null,
        public ?string $url = null,
        public ?string $image = null,
        public ?string $region = null,
        public ?bool $disabilityAccess = null,
        public ?FeedItemCoordinates $coordinates = null,
    ) {
    }
}
