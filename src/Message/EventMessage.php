<?php

namespace App\Message;

use App\Model\Feed\FeedItem;

final class EventMessage
{
    public function __construct(
        private readonly FeedItem $item,
        private readonly bool $forceUpdate = false
    ) {
    }

    public function getItem(): FeedItem
    {
        return $this->item;
    }

    public function isForceUpdate(): bool
    {
        return $this->forceUpdate;
    }
}
