<?php

namespace App\Utils;

class UriHelper
{
    public static function getAbsoluteUrl(string $inputUrl, ?string $base = null): string
    {
        $url = trim($inputUrl);

        if (parse_url($url, PHP_URL_HOST) && parse_url($url, PHP_URL_SCHEME)) {
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
