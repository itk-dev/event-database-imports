<?php

namespace App\Model\Feed;

use Traversable;

final class FeedItemOccurrenceCollection implements \IteratorAggregate
{
    /**
     * @var array<FeedItemOccurrence> $occurrences
     */
    private readonly array $occurrences;

    public function __construct(FeedItemOccurrence ...$occurrences)
    {
        $this->occurrences = $occurrences;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->occurrences);
    }

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
