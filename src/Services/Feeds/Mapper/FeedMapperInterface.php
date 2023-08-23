<?php

namespace App\Services\Feeds\Mapper;

use App\Model\Feed\FeedConfiguration;
use App\Model\Feed\FeedItem;
use CuyZ\Valinor\Mapper\MappingError;

interface FeedMapperInterface
{
    /**
     * Transform raw feed item array into typed FeedItem object.
     *
     * @param array  $data
     *   Array with data from feed
     * @param feedConfiguration $configuration
     *   Feed configuration
     *
     * @throws MappingError
     */
    public function getFeedItemFromArray(array $data, FeedConfiguration $configuration): FeedItem;
}
