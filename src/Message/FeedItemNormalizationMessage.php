<?php

declare(strict_types=1);

namespace App\Message;

use App\Model\Feed\FeedConfiguration;
use App\Model\Feed\FeedItemData;

final readonly class FeedItemNormalizationMessage
{
    public function __construct(
        private FeedConfiguration $configuration,
        private FeedItemData $item,
    ) {
    }

    public function getConfiguration(): FeedConfiguration
    {
        return $this->configuration;
    }

    public function getFeedItemData(): FeedItemData
    {
        return $this->item;
    }
}
