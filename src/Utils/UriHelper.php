<?php

namespace App\Utils;

use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

class UriHelper
{
    /**
     * The image root (relative to the project dir). Image paths are stored relative to it.
     */
    public const string IMAGE_DIR = 'public/images';

    /**
     * The folder in the image root for images uploaded in the admin.
     */
    public const string UPLOAD_FOLDER = 'uploads';

    public function __construct(
        private readonly string $defaultUri,
    ) {
    }

    /**
     * Get the absolute URL of a local image from its stored path (e.g. "uploads/<file>").
     */
    public function getAbsoluteLocalFileUrl(string $file): string
    {
        $urlPackage = new UrlPackage(
            $this->defaultUri.substr(self::IMAGE_DIR, 6),
            new EmptyVersionStrategy(),
        );

        return $urlPackage->getUrl($file);
    }

    public static function getAbsoluteUrl(string $inputUrl, ?string $base = null): string
    {
        $url = trim($inputUrl);

        if ('' === $url) {
            throw new \RuntimeException('Cannot convert empty URL to absolute URL.');
        }

        if (parse_url($url, PHP_URL_HOST) && parse_url($url, PHP_URL_SCHEME)) {
            // URL is absolute, return unmodified
            return $url;
        }

        if (null !== $base) {
            $baseUrl = rtrim($base, '/');
            $path = ltrim($url, '/');

            $url = $baseUrl.'/'.$path;
        }

        if (null === parse_url($url, PHP_URL_SCHEME)) {
            $url .= 'https://'.$url;
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        throw new \RuntimeException(sprintf('Could not convert feed url (%s) to absolute url', $inputUrl));
    }
}
