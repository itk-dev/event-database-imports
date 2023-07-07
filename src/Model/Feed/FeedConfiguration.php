<?php

namespace App\Model\Feed;

final class FeedConfiguration
{
    public readonly string $url;
    public readonly string $timezone;
    public readonly array $mapping;
    public readonly array $defaults;

    public function __toString(): string
    {
        $output = [];

        $output[] = str_repeat('-', 70);
        $output[] = str_repeat('-', 70);

        return implode("\n", $output);
    }
}
