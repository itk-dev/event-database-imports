<?php

namespace App\Model\Feed;

final class FeedItem
{
    public readonly int $id;
    public string $title = '';
    public string $excerpt = '';
    public string $description = '';
    public string $image = '';
    public string $ticketUrl = '';
    public string $url = '';
    public ?\DateTimeImmutable $start = null;
    public ?\DateTimeImmutable $end = null;

    // Properties that is not required in the FeedMapperService.
    public string $feedId = '';
    public ?FeedItemOccurrenceCollection $occurrences = null;

    public function __toString(): string
    {
        $output = [];

        $output[] = str_repeat('-', 70);
        $output[] = 'Feed ID: '.$this->feedId;
        $output[] = 'Id: '.$this->id;
        $output[] = 'Title: '.$this->title;
        $output[] = wordwrap('Excerpt: '.$this->excerpt, 60, "\n         ");
        $output[] = 'Start: '.$this->start?->format('c');
        $output[] = 'End: '.$this->end?->format('c');
        $output[] = 'URL: '.$this->url;
        $output[] = str_repeat('-', 70);

        return implode("\n", $output);
    }
}
