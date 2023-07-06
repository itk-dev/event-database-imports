<?php

namespace App\Services;

use App\Model\FeedItem;

interface FeedMapperInterface
{
    public function getFeedItemFromArray(array $data): FeedItem;
}
