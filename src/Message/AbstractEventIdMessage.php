<?php

declare(strict_types=1);

namespace App\Message;

abstract class AbstractEventIdMessage
{
    public function __construct(
        private readonly int $eventId,
    ) {
    }

    public function getEventId(): int
    {
        return $this->eventId;
    }
}
