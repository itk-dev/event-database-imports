<?php

namespace App\Message;

final class ImageMessage
{
    public function __construct(
        private readonly ?int $eventId,
        private readonly ?int $imageId,
    ) {
    }

    public function getEventId(): ?int
    {
        return $this->eventId;
    }

    public function getImageId(): ?int
    {
        return $this->imageId;
    }
}
