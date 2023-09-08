<?php

namespace App\Services;

interface TagsNormalizerInterface
{
    public function normalize(array $names): array;
}
