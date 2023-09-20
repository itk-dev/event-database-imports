<?php

namespace App\Services;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Image implements ImageInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $publicPath,
    ) {
    }

    public function fetch(string $url): string
    {
        $filesystem = new Filesystem();

        $response = $this->client->request('GET', $url);

        // Responses are lazy: this code is executed as soon as headers are received
        if (200 !== $response->getStatusCode()) {
            throw new \Exception('...');
        }

        // get the response content in chunks and save them in a file
        // response chunks implement Symfony\Contracts\HttpClient\ChunkInterface
        $fileHandler = fopen('/ubuntu.iso', 'w');
        foreach ($this->client->stream($response) as $chunk) {
            fwrite($fileHandler, $chunk->getContent());
        }
        fclose($fileHandler);
    }

    public function remove(\App\Entity\Image $image): bool
    {
        // TODO: Implement remove() method.
    }

    public function transform(\App\Entity\Image $image): bool
    {
        // TODO: Implement transform() method.
    }

    /**
     * Generate safe path to storage file in based on URL.
     *
     * @param string $url
     *   The files URL
     * @param int $depth
     *   The depth of the generated path
     * @param int $size
     *   The size of each element in the generated path
     *
     * @return string
     *   The generated path ending with slash
     */
    private function generatePath(string $url, bool $absolute = false, int $depth = 2, int $size = 8): string
    {
        $hash = $this->hash($url);
        $subPath = implode('/', str_split(strtolower(substr($hash, 0, $size * $depth)), $size)).'/';

        return ($absolute ? $this->publicPath : '').$subPath;
    }

    /**
     * Create hash base on the URL.
     *
     * @param string $url
     *   URL to hash
     *
     * @return string
     *   The hashed value from the URL
     */
    private function hash(string $url): string
    {
        return hash('sha256', $url);
    }
}
