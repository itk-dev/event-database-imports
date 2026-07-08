<?php

namespace App\Service\Feeds\Mapper;

use App\Model\Feed\FeedConfiguration;
use App\Model\Feed\FeedItemData;
use App\Service\Feeds\FeedDefaultsMapper;
use App\Service\Feeds\Mapper\Source\FeedItemSource;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
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
        // Feed datetimes without an explicit offset are parsed by Valinor with
        // DateTimeImmutable::createFromFormat(), which falls back to the ambient
        // PHP timezone. That differs between environments (UTC on the prod
        // worker, Europe/Copenhagen in dev), so naive feed times were stored
        // 1-2h off in production. Interpret the mapping in the feed's declared
        // timezone instead; values that carry their own offset are unaffected.
        $previousTimezone = date_default_timezone_get();
        date_default_timezone_set($configuration->timezone);

        try {
            return (new MapperBuilder())
                ->allowSuperfluousKeys()
                ->allowScalarValueCasting()
                ->allowNonSequentialList()
                ->allowUndefinedValues()
                ->supportDateFormats($configuration->dateFormat)
                ->mapper()
                ->map(
                    FeedItemData::class,
                    Source::iterable((new FeedItemSource($configuration, $this->defaultsMapperService))->normalize($data))
                );
        } catch (MappingError $error) {
            // Get flatten list of all messages through the whole nodes tree
            foreach ($error->messages() as $message) {
                $this->logger->error($message);
            }
            throw $error;
        } finally {
            date_default_timezone_set($previousTimezone);
        }
    }
}
