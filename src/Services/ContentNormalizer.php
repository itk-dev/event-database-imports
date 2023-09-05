<?php

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

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
        return $onWords ? $this->wordSplitter($content, $maxLength) : mb_substr($content, 0, $maxLength);
    }

    /**
     * Trim content length on whole words.
     *
     * @see https://stackoverflow.com/questions/79960/how-to-truncate-a-string-in-php-to-the-word-closest-to-a-certain-number-of-chara
     *
     * @param string $content
     *   The content to trim in length
     * @param int $maxLength
     *   The max length of content to return
     *
     * @return string
     *   The trimmed content wit max length
     */
    private function wordSplitter(string $content, int $maxLength): string
    {
        $parts = preg_split('/([\s\n\r]+)/u', $content, null, PREG_SPLIT_DELIM_CAPTURE);
        $parts_count = count($parts);

        $length = 0;
        $last_part = 0;
        for (; $last_part < $parts_count; ++$last_part) {
            $length += strlen($parts[$last_part]);
            if ($length > $maxLength) { break; }
        }

        return implode(array_slice($parts, 0, $last_part));
    }
}
