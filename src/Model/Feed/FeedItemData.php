<?php

namespace App\Model\Feed;

final class FeedItemData
{
    public function __construct(
        public readonly string $id,
        public ?string $description = null,
        public ?string $excerpt = null,
        public ?string $image = null,
        public ?string $ticketUrl = null,
        public ?string $title = null,
        public ?string $url = null,
        public bool $publicAccess = true,
        /** @var array<FeedItemOccurrence> */
        public array $occurrences = [],
        public ?FeedItemLocation $location = null,
        public ?FeedItemOrganization $organization = null,
        /** @var array<FeedItemOrganization> */
        public array $partners = [],
        public int $feedId = 0,
        /** @var array<string> */
        public array $tags = [],
    ) {
    }
}
