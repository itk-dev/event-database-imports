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
     * Convert plain-text newlines to <br> tags.
     *
     * @param string $content
     *   Plain-text content
     *
     * @return string
     *   Content with newlines converted to <br>
     */
    public function newlinesToHtml(string $content): string;

    /**
     * Trim content length.
     */
    public function trimLength(string $content, int $maxLength, bool $onWords = true): string;

    public function getTextFromHtml(string $htmlContent): string;
}
