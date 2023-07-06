<?php

namespace App\Model;

final class FeedItem
{
    public readonly string $title;
    public readonly string $excerpt;
    public readonly \DateTimeImmutable $start;
    public readonly \DateTimeImmutable $end;

    public function __toString(): string
    {
        $output = [];

        $output[] = str_repeat('-', 41);
        $output[] = 'Title: '.$this->title;
        $output[] = 'Excerpt: '.$this->excerpt;
        $output[] = 'Start: '.$this->start->format('d-m-y H:i:s');
        $output[] = 'End: '.$this->end->format('d-m-y H:i:s');
        $output[] = str_repeat('-', 41);

        return implode("\n", $output);
    }
}
