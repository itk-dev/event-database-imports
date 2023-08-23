<?php

namespace App\Services\Feeds\Parser;

interface FeedParserInterface
{
    /**
     * Parse feed data into array items.
     *
     * @param string $data
     *   The raw feed data or url to download from
     * @param string $pointerPath
     *   The location of the root element (path pointer syntax)
     *
     * @return \Generator
     *   Will yield the items found in the feed
     */
    public function parse(string $data, string $pointerPath = '/-'): \Generator;
}
