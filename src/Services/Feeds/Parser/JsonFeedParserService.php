<?php

namespace App\Services\Feeds\Parser;

use Cerbero\JsonParser\JsonParser;
use Psr\Log\LoggerInterface;

final class JsonFeedParserService implements FeedParserInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function parse(string $data, string $pointerPath = '/-'): \Generator
    {
        $parser = new JsonParser($data);
        $parser->pointer($pointerPath);

        try {
            foreach ($parser as $item) {
                yield $item;
            }
        } catch (\Exception $exception) {
            $this->logger->error('Error parsing JSON feed: {message}', ['message' => $exception->getMessage()]);
            throw $exception;
        }
    }
}
