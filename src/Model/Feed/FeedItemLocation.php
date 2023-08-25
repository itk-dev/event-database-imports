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
    )
    {
    }

    public function __toString(): string
    {
        $output = [];

        $output[] = 'Name: '.$this->name;
        $output[] = 'Url: '.$this->url;
        $output[] = 'Street: '.$this->street;
        $output[] = 'Suite: '.$this->suite;
        $output[] = 'Postal: '.$this->postalCode;
        $output[] = 'City: '.$this->city;
        $output[] = 'Country: '.$this->country;
        $output[] = 'Mail: '.$this->mail;
        $output[] = 'Telephone: '.$this->telephone;

        return implode("\n", $output);
    }
}
