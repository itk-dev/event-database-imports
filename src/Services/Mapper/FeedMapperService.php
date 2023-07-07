<?php

namespace App\Services\Mapper;

use App\Model\Feed\FeedItem;
use App\Services\Mapper\Source\FeedNormalizedSource;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use CuyZ\Valinor\MapperBuilder;

class FeedMapperService implements FeedMapperInterface
{
    /**
     * @inheritDoc
     */
    public function getFeedItemFromArray(array $data, array $mapping, string $dateFormat = 'Y-m-d\TH:i:s'): FeedItem
    {
        try {
            return (new MapperBuilder())
                ->allowSuperfluousKeys()
                ->enableFlexibleCasting()
                ->supportDateFormats($dateFormat)
                ->mapper()
                ->map(
                    FeedItem::class,
                    Source::iterable(new FeedNormalizedSource($data, $mapping))
                );
        } catch (MappingError $error) {
            // @todo: Log mapping error for later debugging.
            // Get flatten list of all messages through the whole nodes tree
            $messages = Messages::flattenFromNode(
                $error->node()
            );
            foreach ($messages as $message) {
                echo $message,"\n";
            }
            throw $error;
        }

        // Apply default values.
    }
}
