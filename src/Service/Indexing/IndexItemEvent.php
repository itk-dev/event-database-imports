<?php

namespace App\Service\Indexing;

final class IndexItemEvent implements IndexItemInterface
{
    public function __construct(
        private readonly string $id,
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
