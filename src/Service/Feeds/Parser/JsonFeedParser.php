<?php

namespace App\Service\Feeds\Parser;

use App\Entity\Feed;
use Cerbero\JsonParser\JsonParser;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use League\Uri\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

final readonly class JsonFeedParser implements FeedParserInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private ClientInterface $client = new Client(),
    ) {
    }

    /**
     * @throws \Exception
     */
    public function parse(Feed $feed, string $data, string $pointerPath = '/-'): \Generator
    {
        $conf = $feed->getConfiguration();

        $clientHeaders = $conf['clientHeaders'] ?? [];

        if (isset($conf['pagination'])) {
            $pagination = $conf['pagination'];
            $page = $pagination['page'] ?? 1;
            $limit = $pagination['limit'] ?? 10;

            do {
                $hasItems = false;
                $url = $this->getNextPageUrl($data, $pagination['pageParameter'], $page, $pagination['limitParameter'], $limit);

                $request = new Request('GET', $url, $clientHeaders);

                foreach ($this->parseUrl($feed, $request, $pointerPath) as $item) {
                    yield $item;
                    $hasItems = true;
                }
                ++$page;
            } while ($hasItems);
        } else {
            $request = new Request('GET', $data, $clientHeaders);

            yield from $this->parseUrl($feed, $request, $pointerPath);
        }
    }

    public function parseUrl(Feed $feed, RequestInterface $request, string $pointerPath = '/-'): \Generator
    {
        try {
            // send() follows redirects and throws on HTTP errors. Passing the request to JsonParser would use
            // Guzzle's PSR-18 sendRequest(), which does neither, so a redirect or error page was parsed as JSON.
            $response = $this->client->send($request, ['stream' => true]);

            $parser = new JsonParser($response);
            $parser->pointer($pointerPath);

            foreach ($parser as $item) {
                yield $item;
            }
        } catch (\Exception $exception) {
            $this->logger->error('Error parsing JSON feed ({id}): {message}', ['id' => $feed->getId(), 'message' => $exception->getMessage()]);
            throw $exception;
        }
    }

    private function getNextPageUrl(string $url, string $pageParameter, int $page, string $limitParameter, int $limit): string
    {
        $uri = Uri::new($url);
        $query = $uri->getQuery();
        if (null === $query) {
            $query = $pageParameter.'='.$page.'&'.$limitParameter.'='.$limit;
        } else {
            $query .= '&'.$pageParameter.'='.$page.'&'.$limitParameter.'='.$limit;
        }

        return $uri->withQuery($query)->toString();
    }
}
