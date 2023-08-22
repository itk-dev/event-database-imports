<?php

namespace App\Model\Feed;

final class FeedCoordinates
{
    public string $lat = '';
    public string $long = '';

    public function __toString(): string
    {
        $output = [];
        $output[] = 'Latitude: '.$this->lat;
        $output[] = 'Longitude: '.$this->long;

        return implode("\n", $output);
    }
}
