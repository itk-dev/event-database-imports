<?php

namespace App\Message;

final class ImageMessage extends AbstractEventIdMessage
{
    public function __construct(
        private readonly int $eventId,
        private readonly ?int $imageId,
    ) {
        parent::__construct($this->eventId);
    }

    public function getImageId(): ?int
    {
        return $this->imageId;
    }
}
