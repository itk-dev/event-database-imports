<?php

namespace App\Message;

final class DailyOccurrenceMessage
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
