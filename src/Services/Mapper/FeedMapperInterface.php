<?php

namespace App\Services\Mapper;

use App\Model\Feed\FeedItem;

interface FeedMapperInterface
{
    public function getFeedItemFromArray(array $data): FeedItem;
}
