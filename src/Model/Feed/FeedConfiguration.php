<?php

namespace App\Model\Feed;

final class FeedConfiguration
{
    public function __construct(
        public readonly string $type,
        public readonly string $url,
        public readonly string $base,
        public readonly string $timezone,
        public readonly string $rootPointer,
        /** @var non-empty-string */
        public readonly string $dateFormat,
        /** @var array list<string> */
        public readonly array $mapping = [],
        /** @var array list<string> */
        public readonly array $defaults = [],
    ) {
    }
}
