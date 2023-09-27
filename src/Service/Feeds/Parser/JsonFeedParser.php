<?php

namespace App\Service\Feeds\Parser;

use App\Entity\Feed;
use Cerbero\JsonParser\JsonParser;
use Psr\Log\LoggerInterface;

final class JsonFeedParser implements FeedParserInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function parse(Feed $feed, string $data, string $pointerPath = '/-'): \Generator
    {
        $parser = new JsonParser($data);
        $parser->pointer($pointerPath);

        try {
            foreach ($parser as $item) {
                yield $item;
            }
        } catch (\Exception $exception) {
            $this->logger->error('Error parsing JSON feed ({id}): {message}', ['id' => $feed->getId(), 'message' => $exception->getMessage()]);
            throw $exception;
        }
    }
}
