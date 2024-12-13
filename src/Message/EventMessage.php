<?php

namespace App\Message;

use App\Model\Feed\FeedItemData;

final class EventMessage
{
    public function __construct(
        private readonly FeedItemData $item,
    ) {
    }

    public function getFeedItemData(): FeedItemData
    {
        return $this->item;
    }
}
