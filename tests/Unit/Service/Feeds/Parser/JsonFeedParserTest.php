<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Feeds\Parser;

use App\Entity\Feed;
use App\Service\Feeds\Parser\JsonFeedParser;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Feeds must follow redirects (Dokk1 moved from www.dokk1.dk to dokk1.dk with a 308), and an HTTP error
 * must fail as an HTTP error, not as a JSON syntax error on the error page.
 */
#[CoversClass(JsonFeedParser::class)]
final class JsonFeedParserTest extends TestCase
{
    public function testFollowsRedirect(): void
    {
        $items = iterator_to_array($this->parser(
            new Response(308, ['Location' => 'https://example.com/feed'], 'Moved Permanently.'),
            new Response(200, ['Content-Type' => 'application/json'], '[{"id": 1}, {"id": 2}]'),
        )->parse(new Feed()->setConfiguration([]), 'https://www.example.com/feed'), false);

        $this->assertSame([['id' => 1], ['id' => 2]], $items);
    }

    public function testHttpErrorIsReportedAsHttpError(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionMessageIsOrContains('404 Not Found');

        iterator_to_array($this->parser(
            new Response(404, [], 'Not Found'),
        )->parse(new Feed()->setConfiguration([]), 'https://example.com/feed'));
    }

    private function parser(Response ...$responses): JsonFeedParser
    {
        return new JsonFeedParser(
            new NullLogger(),
            new Client(['handler' => HandlerStack::create(new MockHandler($responses))]),
        );
    }
}
