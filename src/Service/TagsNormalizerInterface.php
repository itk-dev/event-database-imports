<?php

declare(strict_types=1);

namespace App\Service;

interface TagsNormalizerInterface
{
    public function normalize(array $names): array;
}
