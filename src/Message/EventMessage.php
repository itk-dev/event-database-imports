<?php

namespace App\Message;

use App\Model\Feed\FeedItem;

class EventMessage
{
    public function __construct(
        private readonly FeedItem $item
    ) {
    }

    public function getItem(): FeedItem
    {
        return $this->item;
    }
}
