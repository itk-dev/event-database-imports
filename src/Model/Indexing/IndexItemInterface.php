<?php

namespace App\Model\Indexing;

interface IndexItemInterface
{
    /**
     * Get identifier for this item (should match an ID from the database).
     */
    public function getId(): int;

    /**
     * Convert the index item into an array payload for the indexing engine.
     */
    public function toArray(): array;
}
