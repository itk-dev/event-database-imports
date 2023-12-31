<?php

namespace App\Model\Feed;

final class FeedItemOccurrence
{
    public function __construct(
        public ?\DateTimeImmutable $start = null,
        public ?\DateTimeImmutable $end = null,
        public ?string $price = null,
        public ?string $status = null,
        public ?string $room = null,
    ) {
    }
}
