<?php

namespace App\Services\Feeds;

interface FeedParserInterface
{
    public function parse(string $data, string $pointerPath = '/-'): \Generator;
}
