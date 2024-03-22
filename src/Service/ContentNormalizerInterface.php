<?php

namespace App\Service;

interface ContentNormalizerInterface
{
    /**
     * Sanitize HTML content.
     *
     * @param string $content
     *   The HTML string
     *
     * @return string
     *   Sanitize HTML
     */
    public function sanitize(string $content): string;

    /**
     * Trim content length.
     */
    public function trimLength(string $content, int $maxLength, bool $onWords = true): string;

    public function getTextFromHtml(string $htmlContent): string;
}
