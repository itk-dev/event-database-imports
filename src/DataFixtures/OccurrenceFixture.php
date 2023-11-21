<?php

namespace App\DataFixtures;

use App\Entity\Occurrence;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class OccurrenceFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $occurrence = new Occurrence();
        $occurrence->setEvent($this->getReference(EventFixture::EVENT1))
            ->setStart(new \DateTimeImmutable())
            ->setEnd(new \DateTimeImmutable('2024-12-07T14:30:00+02:00'))
            ->setTicketPriceRange('2024-12-07T15:30:00+02:00')
            ->setRoom('M2-5');
        $manager->persist($occurrence);
        $occurrence = new Occurrence();
        $occurrence->setEvent($this->getReference(EventFixture::EVENT1))
            ->setStart(new \DateTimeImmutable())
            ->setEnd(new \DateTimeImmutable('2024-12-08T10:30:00+02:00'))
            ->setTicketPriceRange('2024-12-08T16:30:00+02:00')
            ->setRoom('M2-6');
        $manager->persist($occurrence);

        $occurrence = new Occurrence();
        $occurrence->setEvent($this->getReference(EventFixture::EVENT2))
            ->setStart(new \DateTimeImmutable())
            ->setEnd(new \DateTimeImmutable('2024-12-08T12:30:00+02:00'))
            ->setTicketPriceRange('2024-12-08T14:30:00+02:00')
            ->setRoom('M2-5');
        $manager->persist($occurrence);

        // Make it stick.
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            EventFixture::class,
        ];
    }
}
