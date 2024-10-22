<?php

namespace App\Message;

use App\Model\Feed\FeedConfiguration;

final class FeedItemDataMessage
{
    public function __construct(
        private readonly int $feedId,
        private readonly FeedConfiguration $configuration,
        private readonly array $data,
        private readonly bool $forceUpdate = false,
    ) {
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

    public function isForceUpdate(): bool
    {
        return $this->forceUpdate;
    }
}
