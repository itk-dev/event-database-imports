<?php

namespace App\Model\Feed;

final readonly class FeedItemOrganization
{
    public function __construct(
        public ?string $name = null,
        public ?string $mail = null,
        public ?string $url = null,
    ) {
    }
}
