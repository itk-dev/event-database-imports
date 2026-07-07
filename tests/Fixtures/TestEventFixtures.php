<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\DataFixtures\OrganizationFixtures;
use App\Entity\Event;
use App\Entity\Organization;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class TestEventFixtures extends Fixture implements DependentFixtureInterface
{
    public const EVENT_ORG_A_1 = 'test-event-org-a-1';
    public const EVENT_ORG_A_2 = 'test-event-org-a-2';
    public const EVENT_ORG_B_1 = 'test-event-org-b-1';
    public const EVENT_ORPHAN = 'test-event-orphan';

    public function load(ObjectManager $manager): void
    {
        $orgA = $this->getReference(OrganizationFixtures::AAKB, Organization::class);
        $orgB = $this->getReference(OrganizationFixtures::DOKK1, Organization::class);

        $this->addReference(
            self::EVENT_ORG_A_1,
            $this->createEvent($manager, 'Org A Event 1', $orgA),
        );
        $this->addReference(
            self::EVENT_ORG_A_2,
            $this->createEvent($manager, 'Org A Event 2', $orgA),
        );
        $this->addReference(
            self::EVENT_ORG_B_1,
            $this->createEvent($manager, 'Org B Event 1', $orgB),
        );
        $this->addReference(
            self::EVENT_ORPHAN,
            $this->createEvent($manager, 'Orphan Event', null),
        );

        $manager->flush();
    }

    public function getDependencies(): array
    {
        // Only OrganizationFixtures is required — events reference organizations,
        // not users. (Tests needing users load TestUserFixtures explicitly.)
        return [
            OrganizationFixtures::class,
        ];
    }

    private function createEvent(ObjectManager $manager, string $title, ?Organization $organization): Event
    {
        $event = new Event();
        $event->setTitle($title)
            ->setDescription('Lorem ipsum dolor sit amet.')
            ->setExcerpt('Lorem ipsum')
            ->setUrl('https://example.com/'.urlencode($title))
            ->setPublicAccess(true)
            ->setUpdatedBy('test')
            ->setEditable(true);

        if (null !== $organization) {
            $event->setOrganization($organization);
        }

        $manager->persist($event);

        return $event;
    }
}
