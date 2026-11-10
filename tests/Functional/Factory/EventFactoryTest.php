<?php

declare(strict_types=1);

namespace App\Tests\Functional\Factory;

use App\DataFixtures\OrganizationFixtures;
use App\Entity\Event;
use App\Entity\Feed;
use App\Entity\FeedItem;
use App\Entity\Organization;
use App\Exception\FactoryException;
use App\Factory\EventFactory;
use App\Model\Feed\FeedItemData;
use App\Tests\Functional\AbstractAdminTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Feed events must meet the same entity constraints as events created in the admin.
 */
#[CoversClass(EventFactory::class)]
final class EventFactoryTest extends AbstractAdminTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([OrganizationFixtures::class]);
    }

    /**
     * A feed item without an organization, from a feed without an organization, is rejected and not saved.
     */
    public function testEventWithoutOrganizationIsRejected(): void
    {
        [$feed, $feedItem] = $this->createFeedWithItem(null);

        try {
            $this->eventFactory()->createOrUpdate($this->itemData($feed), $feedItem);
            $this->fail('Expected a FactoryException for an event without an organization');
        } catch (FactoryException $exception) {
            $this->assertStringContainsString('organization', $exception->getMessage());
        }

        $this->assertNotInstanceOf(Event::class, $this->entityManager()->getRepository(Event::class)->findOneBy(['title' => 'Feed event']));
    }

    /**
     * A feed item without an organization gets the organization of the feed and is saved.
     */
    public function testEventGetsTheFeedOrganization(): void
    {
        $organization = $this->entityManager()->getRepository(Organization::class)->findOneBy([]);
        $this->assertInstanceOf(Organization::class, $organization);
        [$feed, $feedItem] = $this->createFeedWithItem($organization);

        $event = $this->eventFactory()->createOrUpdate($this->itemData($feed), $feedItem);

        $this->assertNotNull($event->getId());
        $this->assertSame($organization->getId(), $event->getOrganization()?->getId());
    }

    /**
     * @return array{Feed, FeedItem}
     */
    private function createFeedWithItem(?Organization $organization): array
    {
        $feed = new Feed();
        $feed->setName('Test feed')
            ->setEnabled(true)
            ->setConfiguration([])
            ->setOrganization($organization);
        $feedItem = new FeedItem($feed, 'item-1', []);
        $feedItem->setLastSeenAt();

        $this->entityManager()->persist($feed);
        $this->entityManager()->persist($feedItem);
        $this->entityManager()->flush();

        return [$feed, $feedItem];
    }

    private function itemData(Feed $feed): FeedItemData
    {
        return new FeedItemData(
            id: 'item-1',
            description: 'Description',
            title: 'Feed event',
            feedId: (int) $feed->getId(),
        );
    }

    private function eventFactory(): EventFactory
    {
        return self::getContainer()->get(EventFactory::class);
    }

    private function entityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine')->getManager();
    }
}
