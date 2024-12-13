<?php

namespace App\Utils;

use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

class UriHelper
{
    public const string UPLOAD_DIR = 'public/images/uploads';

    public function __construct(
        private readonly string $defaultUri,
    ) {
    }

    public function getAbsoluteLocalFileUrl(string $file): string
    {
        $urlPackage = new UrlPackage(
            $this->defaultUri.substr(self::UPLOAD_DIR, 6),
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
