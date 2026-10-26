<?php

declare(strict_types=1);

namespace App\Message;

use App\Model\Feed\FeedConfiguration;

final readonly class FeedItemDataMessage
{
    public function __construct(
        private int $feedId,
        private FeedConfiguration $configuration,
        private array $data,
        private bool $forceUpdate = false,
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
