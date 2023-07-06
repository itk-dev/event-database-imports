<?php

namespace App\Model\Feed;

final class FeedItemOccurrence
{
    public readonly \DateTimeImmutable $start;
    public readonly \DateTimeImmutable $end;

    public function __toString(): string
    {
        $output = [];

        $output[] = str_repeat('-', 41);
        $output[] = 'Start: '.$this->start->format('d-m-y H:i:s');
        $output[] = 'End: '.$this->end->format('d-m-y H:i:s');
        $output[] = str_repeat('-', 41);

        return implode("\n", $output);
    }
}
