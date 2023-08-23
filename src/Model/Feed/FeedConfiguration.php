<?php

namespace App\Model\Feed;

final class FeedConfiguration
{
    public readonly string $type;
    public readonly string $url;
    public readonly string $timezone;
    public readonly string $rootPointer;
    /** @var non-empty-string */
    public readonly string $dateFormat;
    /** @var array list<string> */
    public readonly array $mapping;
    /** @var array list<string> */
    public readonly array $defaults;

    public function __toString(): string
    {
        $output = [];
        $output[] = 'Type: '.$this->type;
        $output[] = 'Url: '.$this->url;

        return implode("\n", $output);
    }
}
