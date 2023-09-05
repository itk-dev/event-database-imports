<?php

namespace App\Model\Feed;

final class FeedItemCoordinates
{
    /**
     * Default constructor.
     *
     * The coordinates are stings because the feeds use different separators and notation for numbers in the input.
     *
     * @param string $lat
     *  Latitude
     * @param string $long
     *   Longitude
     */
    public function __construct(
        public string $lat = '',
        public string $long = '',
    ) {
    }
}
