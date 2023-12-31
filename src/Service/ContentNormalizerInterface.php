<?php

namespace App\Service;

interface ContentNormalizerInterface
{
    /**
     * Normalize HTML content.
     *
     * @param string $content
     *   The HTML string
     *
     * @return string
     *   Normalized and cleaned HTML
     */
    public function normalize(string $content): string;

    /**
     * Trim content length.
     */
    public function trimLength(string $content, int $maxLength, bool $onWords = true): string;
}
