<?php

namespace App\Service;

use App\Exception\FilesystemException;
use App\Exception\ImageFetchException;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class Image implements ImageInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $publicPath,
    ) {
    }

    /**
     * @throws FilesystemException
     * @throws TransportExceptionInterface
     * @throws ImageFetchException
     */
    public function fetch(string $url): string
    {
        $filesystem = new Filesystem();

        try {
            $path = $this->generatePath(url: $url, absolute: true);
            $filesystem->mkdir(
                Path::normalize($path),
                0775
            );
        } catch (IOExceptionInterface $exception) {
            throw new FilesystemException('Unable to create upload folder', $exception->getCode(), $exception);
        }

        $response = $this->client->request('GET', $url);
        if (200 !== $response->getStatusCode()) {
            throw new ImageFetchException(sprintf('Failed to fetch %s with code %s', $url, $response->getStatusCode()), $response->getStatusCode());
        }

        $dest = $path.basename($url);
        $fileHandler = fopen($dest, 'w');
        foreach ($this->client->stream($response) as $chunk) {
            fwrite($fileHandler, $chunk->getContent());
        }
        fclose($fileHandler);

        return $dest;
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
    private function generatePath(string $url, bool $absolute = false, int $depth = 1, int $size = 8): string
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
        $parts = parse_url($url);

        return hash('sha256', $parts['host']);
    }
}
