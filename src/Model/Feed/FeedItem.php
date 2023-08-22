<?php

namespace App\Model\Feed;

final class FeedItem
{
    public readonly int $id;
    public string $description = '';
    public string $excerpt = '';
    public string $image = '';
    public string $ticketUrl = '';
    public string $title = '';
    public string $url = '';
    public ?\DateTimeImmutable $end = null;
    public ?\DateTimeImmutable $start = null;
    /** @var array<FeedItemOccurrence> */
    public array $occurrences = [];
    public ?FeedLocation $location = null;
    public string $feedId = '';
    public string $price = '';
    /** @var array<string> */
    public mixed $tags = [];

    public function __toString(): string
    {
        $output = [];

        $output[] = str_repeat('-', 70);
        $output[] = 'Feed ID: '.$this->feedId;
        $output[] = 'Id: '.$this->id;
        $output[] = 'Title: '.$this->title;
        $output[] = wordwrap('Excerpt: '.$this->excerpt, 60, "\n         ");
        $output[] = 'Ticker: '.$this->ticketUrl;
        $output[] = 'Start: '.$this->start?->format('c');
        $output[] = 'End: '.$this->end?->format('c');
        $output[] = 'URL: '.$this->url;
        $output[] = 'Price: '.$this->price;
        $output[] = 'Tags: '.implode(', ', $this->tags);
        $output[] = 'Occurrence:';
        foreach ($this->occurrences as $occurrence) {
            $output[] = $occurrence;
        }
        $output[] = str_repeat('-', 70);

        return implode("\n", $output);
    }
}
