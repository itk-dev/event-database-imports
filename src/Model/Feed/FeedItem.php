<?php

namespace App\Model\Feed;

final class FeedItem
{
    public function __construct(
        public readonly string $id,
        public ?string $description = null,
        public ?string $excerpt = null,
        public ?string $image = null,
        public ?string $ticketUrl = null,
        public ?string $title = null,
        public string $url = '',
        public bool $public = true,
        public string $landcode = 'da',
        /** @var array<FeedItemOccurrence> */
        public array $occurrences = [],
        public ?FeedItemLocation $location = null,
        public int $feedId = 0,
        /** @var array<string> */
        public array $tags = [],
    ) {
    }
}
