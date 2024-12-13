<?php

namespace App\Message;

use App\Model\Indexing\IndexNames;

final class IndexMessage
{
    public function __construct(
        private readonly int $entityId,
        private readonly IndexNames $index,
    ) {
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function getIndexName(): IndexNames
    {
        return $this->index;
    }
}
