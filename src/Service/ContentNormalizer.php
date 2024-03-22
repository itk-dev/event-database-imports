<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\String\UnicodeString;

final readonly class ContentNormalizer implements ContentNormalizerInterface
{
    public function __construct(
        private HtmlSanitizerInterface $feedHtmlSanitizer,
    ) {
    }

    public function sanitize(string $content): string
    {
        return $this->feedHtmlSanitizer->sanitize($content);
    }

    public function trimLength(string $content, int $maxLength, bool $onWords = true): string
    {
        $str = new UnicodeString($content);

        return $onWords ? $this->wordSplitter($str, $maxLength) : $str->trim()->truncate($maxLength)->toString();
    }

    public function getTextFromHtml(string $htmlContent): string
    {
        $crawler = new Crawler($htmlContent);

        return $crawler->filterXPath('//text()')->text();
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
