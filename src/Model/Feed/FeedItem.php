<?php

namespace App\Model\Feed;

final class FeedItem
{
    public readonly int $id;
    public readonly string $title;
    public readonly string $excerpt;
    public readonly string $description;
    public readonly string $image;
    public readonly string $ticketUrl;
    public readonly string $url;
    public readonly \DateTimeImmutable $start;
    public readonly \DateTimeImmutable $end;

    // Properties that is not required in the FeedMapperService.
    public string $feedId = '';
    public ?FeedItemOccurrenceCollection $occurrences = null;

    public function __toString(): string
    {
        $output = [];

        $output[] = str_repeat('-', 41);
        $output[] = 'Title: '.$this->title;
        $output[] = 'Excerpt: '.$this->excerpt;
        $output[] = 'Start: '.$this->start->format('d-m-y H:i:s');
        $output[] = 'End: '.$this->end->format('d-m-y H:i:s');
        $output[] = 'URL: '.$this->url;
        $output[] = str_repeat('-', 41);

        return implode("\n", $output);
    }
}
