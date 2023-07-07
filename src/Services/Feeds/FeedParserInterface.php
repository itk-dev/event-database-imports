<?php

namespace App\Services\Feeds;

interface FeedParserInterface
{
    /**
     * Parse feed data.
     *
     * @param string $data
     * @param string $pointerPath
     *
     * @return \Generator
     */
    public function parse(string $data, string $pointerPath = '/-'): \Generator;
}
