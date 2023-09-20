<?php

namespace App\Service;

interface TagsNormalizerInterface
{
    public function normalize(array $names): array;
}
