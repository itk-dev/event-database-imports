<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Occurrence;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class OccurrenceFixture extends Fixture implements DependentFixtureInterface
{
    public const OCCURRENCE_241207 = 'OCCURRENCE_241207';
    public const OCCURRENCE_241108 = 'OCCURRENCE_241108';
    public const OCCURRENCE_241208 = 'OCCURRENCE_241208';

    public function load(ObjectManager $manager): void
    {
        $this->createOccurrence(
            $manager,
            '2024-12-07T14:30:00+02:00',
            '2024-12-07T15:30:00+02:00',
            '10.000 Kr.',
            'M2-5',
            true,
            EventFixture::EVENT1,
            self::OCCURRENCE_241207
        );
        $this->createOccurrence(
            $manager,
            '2024-11-08T10:30:00+02:00',
            '2024-11-08T16:30:00+02:00',
            'Free or 100',
            'M2-6',
            true,
            EventFixture::EVENT1,
            self::OCCURRENCE_241108
        );
        $this->createOccurrence($manager,
            '2024-12-08T12:30:00+02:00',
            '2024-12-08T14:30:00+02:00',
            'Free in December',
            'M2-5',
            true,
            EventFixture::EVENT2,
            self::OCCURRENCE_241208
        );

        // Make it stick.
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            EventFixture::class,
        ];
    }

    /**
     * Creates a new occurrence and persists it to the database.
     *
     * @param ObjectManager $manager
     *   The object manager used for persisting the occurrence
     * @param string $start
     *   The start date and time of the occurrence
     * @param string $end
     *   The end date and time of the occurrence
     * @param string $price
     *   The ticket price range of the occurrence
     * @param string $room
     *   The room of the occurrence
     * @param bool $editable
     *   Whether the occurrence is editable
     * @param string $event
     *   The reference of the event associated with the occurrence
     * @param string $reference
     *   The reference for the occurrence
     *
     * @throws \Exception
     */
    private function createOccurrence(ObjectManager $manager, string $start, string $end, string $price, string $room, bool $editable, string $event, string $reference): void
    {
        $occurrence = new Occurrence();
        $occurrence->setStart(new \DateTimeImmutable($start))
            ->setEnd(new \DateTimeImmutable($end))
            ->setTicketPriceRange($price)
            ->setRoom($room)
            ->setEditable($editable)
            ->setEvent($this->getReference($event, Event::class));

        $manager->persist($occurrence);
        $this->addReference($reference, $occurrence);
    }
}
