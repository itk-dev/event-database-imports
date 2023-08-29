<?php

namespace App\Message;

use App\Model\Feed\FeedConfiguration;

class FeedItemDataMessage
{
    public function __construct(
        private readonly int $feedId,
        private readonly FeedConfiguration $configuration,
        private readonly array $data,
    )
    {
    }

    public function getFeedId(): int
    {
        return $this->feedId;
    }

    public function getConfiguration(): FeedConfiguration
    {
        return $this->configuration;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
