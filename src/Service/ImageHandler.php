<?php

namespace App\Service;

use App\Entity\Image;
use App\Exception\FilesystemException;
use App\Exception\ImageFetchException;
use App\Exception\ImageMineTypeException;
use Liip\ImagineBundle\Message\WarmupCache;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ImageHandler implements ImageHandlerInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $publicPath,
        private readonly array $allowedMineTypes,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ImageFetchException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws FilesystemException
     * @throws ImageMineTypeException
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
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new ImageFetchException(sprintf('Failed to fetch %s with code %s', $url, $response->getStatusCode()), $response->getStatusCode());
        }

        $headers = $response->getHeaders();
        $dest = $path.$this->generateLocalFilename($url, $this->detectMimetypes($headers));

        $fetchFile = true;
        if ($filesystem->exists($dest)) {
            $size = intval(reset($headers['content-length']));
            if ($size === filesize($dest)) {
                // File exist with the same file size.
                $fetchFile = false;
            }
        }

        // Only download files if changes detected in existing file.
        if ($fetchFile) {
            $fileHandler = fopen($dest, 'w');
            foreach ($this->client->stream($response) as $chunk) {
                fwrite($fileHandler, $chunk->getContent());
            }
            fclose($fileHandler);
        }

        return $this->getRelativePath($dest);
    }

    public function remove(Image $image): bool
    {
        // TODO: Implement remove() method.
        return false;
    }

    /**
     * Generate image transformation using message queue.
     *
     * @param Image $image
     *   The image entity to make transformations on
     */
    public function transform(Image $image): void
    {
        $path = $image->getLocal();
        if (!is_null($path)) {
            $this->messageBus->dispatch(new WarmupCache($this->getRelativePath($path)));
        }
    }

    /**
     * Get relative path from absolute path.
     *
     * @param string $path
     *   The absolute path
     *
     * @return string
     *   Path stripped the image web-root
     */
    private function getRelativePath(string $path): string
    {
        return preg_replace('/^'.preg_quote($this->publicPath, '/').'/', '', $path);
    }

    /**
     * Generate safe path to storage file in based on URL.
     *
     * @param string $url
     *   The files URL
     * @param int<1, max> $depth
     *   The depth of the generated path
     * @param int<1, max> $size
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

        return hash('sha256', $parts['host'] ?? 'unknown');
    }

    /**
     * Create local filename based on URL and mine-type.
     *
     * @param string $url
     *   URL for the file to generate name for
     * @param string $mimetype
     *   The files mine-type
     *
     * @return string
     *   Generated local filename
     *
     * @throws ImageMineTypeException
     */
    private function generateLocalFilename(string $url, string $mimetype): string
    {
        if (!in_array($mimetype, $this->allowedMineTypes)) {
            throw new ImageMineTypeException(sprintf('The mine type "%s" is not supported', $mimetype));
        }
        $ext = (new MimeTypes())->getExtensions($mimetype)[0];

        return hash('sha256', $url).'.'.$ext;
    }

    /**
     * Try to detect mime type based on http headers.
     *
     * @param array $headers
     *   Array of http headers
     *
     * @return string
     *   The mimetype or the empty string if not found
     */
    private function detectMimetypes(array $headers): string
    {
        $type = false;
        if (isset($headers['content-type'])) {
            $type = reset($headers['content-type']);
        }

        if ($type) {
            return $type;
        }

        return '';
    }
}
