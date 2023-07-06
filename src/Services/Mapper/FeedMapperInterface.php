<?php

namespace App\Services\Mapper;

use App\Model\Feed\FeedItem;

interface FeedMapperInterface
{
    /**
     * Map data array into type FeedItem object.
     *
     * @todo: Make mapping dynamic with feed configuration.
     * @todo: Convert mapping array to value object.
     *
     * @param array $data
     *   Array with data from feed.
     * @param array $mapping
     *   Field mapping from data to FeedItem
     *
     * @return FeedItem
     */
    public function getFeedItemFromArray(array $data, array $mapping): FeedItem;
}
