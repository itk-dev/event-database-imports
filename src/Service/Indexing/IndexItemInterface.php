<?php

namespace App\Service\Indexing;

interface IndexItemInterface
{
    /**
     * Get identifier for this item (should match an ID from the database).
     */
    public function getId(): ?int;
}
