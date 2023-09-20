<?php

namespace App\Service;

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
            // The truncate function above has returned a string longer then max length. Remove the last space character
            // and any non-space characters from end of string.
            $str = new UnicodeString(preg_replace('/\s+\S+$/u', '', $str->toString()));
            // Truncate the string hard if the string is still longer than the max length, e.g. if it's a single very
            // long word.
            if ($str->length() > $maxLength) {
                $str = $str->truncate($maxLength);
            }
        }

        return $str->toString();
    }
}
