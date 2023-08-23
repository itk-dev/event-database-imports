<?php

namespace App\Services\Feeds\Mapper;

use App\Model\Feed\FeedItem;

interface FeedMapperInterface
{
    /**
     * Map data array into type FeedItem object.
     *
     * @todo: Make mapping dynamic with feed configuration.
     *
     * @todo: Convert mapping array to value object.
     *
     * @param array  $data
     *  Array with data from feed
     * @param array  $mapping
     *   Field mapping from data to FeedItem
     * @param string $dateFormat
     *   Date format for date fields
     */
    public function getFeedItemFromArray(array $data, array $mapping, string $dateFormat = 'Y-m-d\TH:i:s'): FeedItem;
}
