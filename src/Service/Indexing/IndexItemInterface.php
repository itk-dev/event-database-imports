<?php

namespace App\Service\Indexing;

interface IndexItemInterface
{
    public function getId(): int;

    /**
     * Convert the index item into an array payload for the indexing engine.
     */
    public function toArray(): array;
}
