<?php

namespace App\Services\Feeds;

interface TagsNormalizerInterface
{
    public function normalize(array $names);
}
