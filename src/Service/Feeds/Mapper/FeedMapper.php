<?php

namespace App\Service\Feeds\Mapper;

use App\Model\Feed\FeedConfiguration;
use App\Model\Feed\FeedItemData;
use App\Service\Feeds\FeedDefaultsMapper;
use App\Service\Feeds\Mapper\Source\FeedItemSource;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use CuyZ\Valinor\MapperBuilder;
use Psr\Log\LoggerInterface;

final readonly class FeedMapper implements FeedMapperInterface
{
    public function __construct(
        private FeedDefaultsMapper $defaultsMapperService,
        private LoggerInterface $logger,
    ) {
    }

    public function getFeedItemFromArray(array $data, FeedConfiguration $configuration): FeedItemData
    {
        try {
            return (new MapperBuilder())
                ->allowSuperfluousKeys()
                ->enableFlexibleCasting()
                ->supportDateFormats($configuration->dateFormat)
                ->mapper()
                ->map(
                    FeedItemData::class,
                    Source::iterable((new FeedItemSource($configuration, $this->defaultsMapperService))->normalize($data))
                );
        } catch (MappingError $error) {
            // Get flatten list of all messages through the whole nodes tree
            $messages = Messages::flattenFromNode($error->node());
            foreach ($messages as $message) {
                $this->logger->error($message);
            }
            throw $error;
        }
    }
}
