<?php

namespace App\Message;

final class GeocoderMessage
{
    public function __construct(
        private readonly int $eventId,
    ) {
    }

    public function getEventId(): ?int
    {
        return $this->eventId;
    }
}
