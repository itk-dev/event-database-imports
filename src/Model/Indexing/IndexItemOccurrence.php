<?php

namespace App\Model\Indexing;

final class IndexItemOccurrence implements IndexItemInterface
{
    public function __construct(
        private readonly int $id,
        public \DateTimeImmutable $start,
        public \DateTimeImmutable $end,
        public string $room,
        public string $status,
        public string $ticketPriceRange,

        public \DateTimeImmutable $created,
        public \DateTimeImmutable $updated,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function toArray(): array
    {
        // TODO: Implement toArray() method.

        return [];
    }
}
