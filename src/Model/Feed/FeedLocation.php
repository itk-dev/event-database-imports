<?php

namespace App\Model\Feed;

final class FeedLocation
{
    public string $city = '';
    public string $country = '';
    public int $postalCode = 0;
    public string $street = '';
    public string $suite = '';
    public string $name = '';
    public string $mail = '';
    public string $telephone = '';
    public string $url = '';
    public string $image = '';
    public string $logo = '';
    public string $region = '';
    public ?FeedCoordinates $coordinates = null;

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
