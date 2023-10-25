<?php

namespace App\Model\Indexing;

final class IndexItemEvent implements IndexItemInterface
{
    public function __construct(
        private readonly int $id,
        public string $excerpt,
        public string $description,
        public string $ticketUrl,
        public string $imageUrl,
        public string $url,
        public string $public,
        public \DateTimeImmutable $created,
        public \DateTimeImmutable $updated,
        public IndexItemLocation $location,
        public IndexItemOrganizer $organizer,
        /** @var array<IndexItemOccurrence> $occurrences */
        public array $occurrences,
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
