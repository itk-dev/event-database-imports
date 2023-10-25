<?php

namespace App\Model\Indexing;

final class IndexItemOrganizer implements IndexItemInterface
{
    public function __construct(
        private readonly int $id,
        public string $name,
        public string $mail,
        public string $url,
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
