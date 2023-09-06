<?php

namespace App\Model\Feed;

final class FeedItem
{
    public function __construct(
        public readonly int $id,
        public string $description = '',
        public string $excerpt = '',
        public string $image = '',
        public string $ticketUrl = '',
        public string $title = '',
        public string $url = '',
        public bool $public = true,
        public ?\DateTimeImmutable $end = null,
        public ?\DateTimeImmutable $start = null,
        /** @var array<FeedItemOccurrence> */
        public array $occurrences = [],
        public ?FeedItemLocation $location = null,
        public int $feedId = 0,
        public string $price = '',
        /** @var array<string> */
        public array $tags = [],
    ) {
    }
}
