<?php

namespace App\Services\Mapper;

use App\Model\Feed\FeedItem;

interface FeedMapperInterface
{
    /**
     * Map data array into type FeedItem object.
     *
     * @todo: Make mapping dynamic with feed configuration.
     *
     * @param array $data
     *   Array with data from feed.
     *
     * @return FeedItem
     */
    public function getFeedItemFromArray(array $data): FeedItem;
}
