<?php

declare(strict_types=1);

namespace App\Model\Feed;

class FeedItemTag
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
