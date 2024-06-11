<?php

namespace App\Message;

use App\Model\Feed\FeedConfiguration;
use App\Model\Feed\FeedItem;

final class FeedNormalizationMessage
{
    public function __construct(
        private readonly FeedConfiguration $configuration,
        private readonly FeedItem $item,
        private readonly bool $forceUpdate,
    ) {
    }

    public function getConfiguration(): FeedConfiguration
    {
        return $this->configuration;
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
