<?php

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\String\UnicodeString;

final class ContentNormalizer implements ContentNormalizerInterface
{
    public function __construct(
        private readonly HtmlSanitizerInterface $feedHtmlSanitizer,
    ) {
    }

    public function normalize(string $content): string
    {
        return $this->feedHtmlSanitizer->sanitize($content);
    }

    public function trimLength(string $content, int $maxLength, bool $onWords = true): string
    {
        $str = new UnicodeString($content);

        return $onWords ? $this->wordSplitter($str, $maxLength) : $str->truncate($maxLength)->trim()->toString();
    }

    /**
     * Trim content length on whole words.
     *
     * @param UnicodeString $str
     *   The content to trim in length
     * @param int $maxLength
     *   The max length of content to return
     *
     * @return string
     *   The trimmed content wit max length
     */
    private function wordSplitter(UnicodeString $str, int $maxLength): string
    {
        $str = $str->truncate($maxLength, '', false)->trim();

        if ($str->length() > $maxLength) {
            // As the truncate function above, have returned a string longer then max length. Remove the last word from
            // the string to ensure that it is below max length.
            return preg_replace('/\W\w+\s*(\W*)$/', '$1', $str->toString());
        }

        return $str->toString();
    }
}
