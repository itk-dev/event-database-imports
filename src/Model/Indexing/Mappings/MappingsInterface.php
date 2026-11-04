<?php

declare(strict_types=1);

namespace App\Model\Indexing\Mappings;

interface MappingsInterface
{
    public static function getProperties(): array;
}
