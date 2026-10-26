<?php

declare(strict_types=1);

namespace App\Message;

use App\Model\Indexing\IndexNames;

final readonly class IndexMessage
{
    public function __construct(
        private int $entityId,
        private IndexNames $index,
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
