<?php

namespace App\Model\Feed;

final class FeedItemLocation
{
    public function __construct(
        public string $city = '',
        public string $country = 'Denmark',
        public int $postalCode = 0,
        public string $street = '',
        public string $suite = '',
        public string $name = '',
        public string $mail = '',
        public string $telephone = '',
        public string $url = '',
        public string $image = '',
        public string $logo = '',
        public string $region = '',
        public ?FeedItemCoordinates $coordinates = null,
    ) {
    }
}
