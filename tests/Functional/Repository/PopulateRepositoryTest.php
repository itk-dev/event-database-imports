<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\DataFixtures\OrganizationFixtures;
use App\Entity\Event;
use App\Repository\EventRepository;
use App\Tests\Fixtures\TestEventFixtures;
use App\Tests\Fixtures\TestUserFixtures;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Characterizes AbstractPopulateRepository (the ES read-model population query),
 * which had no direct coverage. Exercised through a concrete repository
 * (EventRepository). Guards the id-ascending ordering and limit/offset paging
 * that the indexing populate step relies on — including across the upcoming
 * Criteria::ASC -> Order enum change and the Doctrine 3 upgrade.
 */
final class PopulateRepositoryTest extends KernelTestCase
{
    private function repository(): EventRepository
    {
        $repository = self::getContainer()->get(EventRepository::class);
        self::assertInstanceOf(EventRepository::class, $repository);

        return $repository;
    }

    private function loadEvents(): void
    {
        $tool = self::getContainer()->get(\Liip\TestFixturesBundle\Services\DatabaseToolCollection::class)->get();
        $tool->loadFixtures([
            OrganizationFixtures::class,
            TestUserFixtures::class,
            TestEventFixtures::class,
        ]);
    }

    public function testCountToPopulateReturnsTotal(): void
    {
        $this->loadEvents();

        // TestEventFixtures creates four events.
        self::assertSame(4, $this->repository()->countToPopulate([]));
    }

    public function testFindToPopulateReturnsIdAscending(): void
    {
        $this->loadEvents();

        $events = $this->repository()->findToPopulate([], 100, 0);

        $ids = array_map(static fn (Event $e): int => (int) $e->getId(), $events);
        $sorted = $ids;
        sort($sorted);
        self::assertSame($sorted, $ids, 'findToPopulate must return events ordered by id ascending');
    }

    public function testFindToPopulateRespectsLimitAndOffset(): void
    {
        $this->loadEvents();

        $all = $this->repository()->findToPopulate([], 100, 0);
        self::assertGreaterThanOrEqual(4, count($all));

        $firstTwo = $this->repository()->findToPopulate([], 2, 0);
        $nextTwo = $this->repository()->findToPopulate([], 2, 2);

        self::assertCount(2, $firstTwo);
        self::assertCount(2, $nextTwo);
        // Paging is stable and non-overlapping.
        self::assertSame($all[0]->getId(), $firstTwo[0]->getId());
        self::assertSame($all[2]->getId(), $nextTwo[0]->getId());
    }
}
