<?php

declare(strict_types=1);

namespace App\Model\Indexing\Criteria;

use App\Model\Indexing\IndexNames;

class PopulateCriteriaFactory
{
    public function getPopulateCriteria(string $name): array
    {
        $index = IndexNames::from($name);

        return match ($index) {
            // Custom filter criteria can be added here. E.g ['published' => true].
            default => [],
        };
    }
}
