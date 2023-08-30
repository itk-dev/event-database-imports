<?php

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

class ContentNormalizer implements ContentNormalizerInterface
{
    public function __construct(
        private readonly HtmlSanitizerInterface $feedHtmlSanitizer,
    ) {
    }

    public function normalize(string $content): string
    {
        return $this->feedHtmlSanitizer->sanitize($content);
    }
}
