<?php

namespace App\Message;

use App\Service\Feeds\Reader\FeedReaderInterface;

class ReadFeedMessage
{
    public function __construct(
        public readonly int $feedId,
        public readonly int $limit = FeedReaderInterface::DEFAULT_OPTION,
        public readonly bool $force = false,
    ) {
    }
}
