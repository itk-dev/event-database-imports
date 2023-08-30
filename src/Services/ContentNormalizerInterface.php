<?php

namespace App\Services;

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
}
