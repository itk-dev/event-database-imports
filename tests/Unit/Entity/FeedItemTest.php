<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Feed;
use App\Entity\FeedItem;
use PHPUnit\Framework\TestCase;

final class FeedItemTest extends TestCase
{
    /**
     * "Now" timestamps must be created in UTC (the model timezone) rather than
     * the ambient PHP default timezone, which differs between environments
     * (Europe/Copenhagen locally, UTC on the server).
     */
    public function testSetLastSeenAtUsesUtc(): void
    {
        $item = new FeedItem(new Feed(), 'feed-item-id', []);

        $item->setLastSeenAt();

        $lastSeenAt = $item->getLastSeenAt();
        self::assertInstanceOf(\DateTimeImmutable::class, $lastSeenAt);
        self::assertSame('UTC', $lastSeenAt->getTimezone()->getName());
    }
}
