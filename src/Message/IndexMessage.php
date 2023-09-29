<?php

namespace App\Message;

final class IndexMessage
{
    public function __construct(
        private readonly int $eventId
    ) {
    }

    public function getEventId(): int
    {
        return $this->eventId;
    }
}
