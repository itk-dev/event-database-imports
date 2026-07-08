<?php

declare(strict_types=1);

namespace App\Message;

use App\Model\Feed\FeedItemData;

final readonly class EventMessage
{
    public function __construct(
        private FeedItemData $item,
    ) {
    }

    public function getFeedItemData(): FeedItemData
    {
        return $this->item;
    }
}
