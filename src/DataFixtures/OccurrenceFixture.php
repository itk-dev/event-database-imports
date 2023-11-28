<?php

namespace App\DataFixtures;

use App\Entity\Occurrence;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class OccurrenceFixture extends Fixture
{
    public const OCCURRENCE_241207 = 'OCCURRENCE_241207';
    public const OCCURRENCE_241108 = 'OCCURRENCE_241108';
    public const OCCURRENCE_241208 = 'OCCURRENCE_241208';

    public function load(ObjectManager $manager): void
    {
        $occurrence = new Occurrence();
        $occurrence->setStart(new \DateTimeImmutable('2024-12-07T14:30:00+02:00'))
            ->setEnd(new \DateTimeImmutable('2024-12-07T15:30:00+02:00'))
            ->setTicketPriceRange('10.000 Kr.')
            ->setRoom('M2-5')
            ->setEditable(true);
        $manager->persist($occurrence);
        $this->addReference(self::OCCURRENCE_241207, $occurrence);

        $occurrence = new Occurrence();
        $occurrence->setStart(new \DateTimeImmutable('2024-11-08T10:30:00+02:00'))
            ->setEnd(new \DateTimeImmutable('2024-11-08T16:30:00+02:00'))
            ->setTicketPriceRange('Free or 100')
            ->setRoom('M2-6')
            ->setEditable(true);
        $manager->persist($occurrence);
        $this->addReference(self::OCCURRENCE_241108, $occurrence);

        $occurrence = new Occurrence();
        $occurrence->setStart(new \DateTimeImmutable('2024-12-08T12:30:00+02:00'))
            ->setEnd(new \DateTimeImmutable('2024-12-08T14:30:00+02:00'))
            ->setTicketPriceRange('Free in December')
            ->setRoom('M2-5')
            ->setEditable(true);
        $manager->persist($occurrence);
        $this->addReference(self::OCCURRENCE_241208, $occurrence);

        // Make it stick.
        $manager->flush();
    }
}
