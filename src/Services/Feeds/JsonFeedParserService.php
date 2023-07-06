<?php

namespace App\Services\Feeds;

use App\Services\FeedMapperInterface;
use Cerbero\JsonParser\JsonParser;

class JsonFeedParserService implements FeedParserInterface
{
    public function __construct(
        private readonly FeedMapperInterface $feedMapper,
    ) {
    }

    public function parse(string $data, string $pointerPath = '/-'): \Generator
    {
        $parser = new JsonParser($data);
        $parser->pointer($pointerPath);

        try {
            foreach ($parser as $item) {
                yield $this->feedMapper->getFeedItemFromArray($item);
            }
        } catch (\Exception $exception) {
            // @todo: Log parsing error for later processing or debugging.
        }
    }
}
