<?php

namespace App\Model\Feed;

final readonly class FeedPagination
{
    public function __construct(
        public ?string $pageParameter,
        public ?string $limitParameter,
        public int $page = 1,
        public int $limit = 10,
    ) {
        if ((null !== $this->pageParameter && null === $this->limitParameter)
           || (null === $this->pageParameter && null !== $this->limitParameter)) {
            throw new \InvalidArgumentException('You must supply either none or both "pageParameter" and a "limitParameter,');
        }
    }

    public function supportsPagination(): bool
    {
        return null !== $this->pageParameter && null !== $this->limitParameter;
    }
}
