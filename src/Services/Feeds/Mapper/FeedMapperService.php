<?php

namespace App\Services\Feeds\Mapper;

use App\Model\Feed\FeedConfiguration;
use App\Model\Feed\FeedItem;
use App\Services\Feeds\FeedDefaultsMapperService;
use App\Services\Feeds\Mapper\Source\FeedItemSource;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use CuyZ\Valinor\MapperBuilder;

class FeedMapperService implements FeedMapperInterface
{
    public function __construct(
        private readonly FeedDefaultsMapperService $defaultsMapperService
    ) {
    }

    public function getFeedItemFromArray(array $data, FeedConfiguration $configuration): FeedItem
    {
        try {
            return (new MapperBuilder())
                ->allowSuperfluousKeys()
                ->enableFlexibleCasting()
                ->supportDateFormats($configuration->dateFormat)
                ->mapper()
                ->map(
                    FeedItem::class,
                    Source::iterable((new FeedItemSource($configuration, $this->defaultsMapperService))->normalize($data))
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
    }
}
