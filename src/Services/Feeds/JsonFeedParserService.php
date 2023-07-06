<?php

namespace App\Services\Feeds;

use App\Services\Mapper\FeedMapperInterface;
use Cerbero\JsonParser\JsonParser;

class JsonFeedParserService implements FeedParserInterface
{
    public function parse(string $data, string $pointerPath = '/-'): \Generator
    {
        $parser = new JsonParser($data);
        $parser->pointer($pointerPath);

        try {
            foreach ($parser as $item) {
                yield $item;
            }
        } catch (\Exception $exception) {
            // @todo: Log parsing error for later processing or debugging.
            throw $exception;
        }
    }
}
