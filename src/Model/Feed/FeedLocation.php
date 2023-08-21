<?php

namespace App\Model\Feed;

class FeedLocation
{
    public string $city = '';
    public string $country = '';
    public int $postalCode = 0;
    public string $street = '';
    public string $suite = '';
    public string $name = '';
    public string $mail = '';
    public ?FeedCoordinates $coordinates = null;

    public function __toString(): string
    {
        $output = [];

        $output[] = 'Street: '.$this->street;
        $output[] = 'Suite: '.$this->suite;
        $output[] = 'Postal: '.$this->postalCode;
        $output[] = 'City: '.$this->city;
        $output[] = 'Country: '.$this->country;

        return implode("\n", $output);
    }
}
