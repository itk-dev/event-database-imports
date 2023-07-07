<?php

namespace App\Model\Feed;

final class FeedItemOccurrenceCollection implements \IteratorAggregate
{
    /**
     * @var array<FeedItemOccurrence>
     */
    private readonly array $occurrences;

    public function __construct(FeedItemOccurrence ...$occurrences)
    {
        $this->occurrences = $occurrences;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->occurrences);
    }
}
