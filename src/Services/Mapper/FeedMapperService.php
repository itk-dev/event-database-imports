<?php

namespace App\Services\Mapper;

use App\Model\Feed\FeedItem;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;

class FeedMapperService implements FeedMapperInterface
{
    public function getFeedItemFromArray(array $data, array $mapping): FeedItem
    {
        try {
            return (new MapperBuilder())
                ->allowSuperfluousKeys()
                ->supportDateFormats('Y-m-d\TH:i:s')
                ->mapper()
                ->map(
                    FeedItem::class,
                    Source::array($data)->map($mapping)
                );
        } catch (MappingError $error) {
            // @todo: Log mapping error for later debugging.
            throw $error;
        }
    }
}
