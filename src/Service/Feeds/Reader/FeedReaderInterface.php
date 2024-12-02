<?php

namespace App\Service\Feeds\Reader;

use App\Entity\Feed;
use CuyZ\Valinor\Mapper\MappingError;

interface FeedReaderInterface
{
    public const int DEFAULT_OPTION = -1;

    /**
     * Get enabled feed entities.
     *
     * @return array<Feed>
     */
    public function getEnabledFeeds(int $limit, bool $force = false, array $feedIds = []): array;

    /**
     * Load enabled feed entities.
     *
     * @throws MappingError
     */
    public function readFeeds(int $limit = self::DEFAULT_OPTION, bool $force = false, array $feedIds = []): iterable;

    /**
     * Read feed.
     *
     * @throws MappingError
     */
    public function readFeed(Feed $feed, int $limit, bool $force = false): iterable;
}
