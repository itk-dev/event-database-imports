<?php

namespace App\Model\Feed;

final class FeedItemOccurrence
{
    public ?\DateTimeImmutable $start = null;
    public ?\DateTimeImmutable $end = null;
    public string $price = '';

    public function __toString(): string
    {
        $output = [];

        $output[] = str_repeat('-', 41);
        $output[] = 'Start: '.$this->start?->format('d-m-y H:i:s');
        $output[] = 'End: '.$this->end?->format('d-m-y H:i:s');
        $output[] = 'Price: '.$this->price;
        $output[] = str_repeat('-', 41);

        return implode("\n", $output);
    }
}
