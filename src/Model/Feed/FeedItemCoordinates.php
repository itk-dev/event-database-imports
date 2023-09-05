<?php

namespace App\Model\Feed;

final class FeedItemCoordinates
{
    /**
     * Default constructor.
     *
     * The coordinates are stings because the feeds use different separators and notation for numbers in the input.
     */
    public function __construct(
        public string $latitude = '',
        public string $longitude = '',
    ) {
    }
}
