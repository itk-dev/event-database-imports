<?php

namespace App\Model\Feed;

final class FeedItem
{
    public function __construct(
        public readonly int $id,
        public ?string $description = null,
        public ?string $excerpt = null,
        public ?string $image = null,
        public ?string $ticketUrl = null,
        public ?string $title = null,
        public ?string $url = null,
        public bool $public = true,
        public string $landcode = 'da',
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
