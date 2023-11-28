<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Event;
use App\Entity\Occurrence;
use App\Factory\OccurrencesFactory;
use App\Model\Feed\FeedItemOccurrence;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(OccurrencesFactory::class)]
final class OccurrencesFactoryTest extends KernelTestCase
{
    /**
     * @throws \Exception
     */
    public function testOccurrenceUpdate()
    {
        $itemOccurrence = new FeedItemOccurrence();
        $itemOccurrence->start = new \DateTimeImmutable('2023-01-19T14:00:00+00:00');
        $itemOccurrence->end = new \DateTimeImmutable('2023-01-19T15:30:00+00:00');
        $itemOccurrence->price = '200';
        $input = [$itemOccurrence];

        $eventOccurrence = new Occurrence();
        $eventOccurrence->setStart(new \DateTimeImmutable('2023-01-19T14:00:00+00:00'))
            ->setEnd(new \DateTimeImmutable('2023-01-19T15:30:00+00:00'))
            ->setTicketPriceRange('400');

        $event = new Event();
        $event->addOccurrence($eventOccurrence);

        $factory = $this->getFactory();
        $factory->createOrLookup($input, $event);

        // Check that object is the same and the price has been updated.
        $this->assertEquals($eventOccurrence, $event->getOccurrences()[0]);
        $this->assertCount(count($input), $event->getOccurrences());
        $this->assertEquals($itemOccurrence->price, $event->getOccurrences()[0]->getTicketPriceRange());
        $this->assertNotEquals('400', $event->getOccurrences()[0]->getTicketPriceRange());
    }

    /**
     * @throws \Exception
     */
    public function testOccurrenceAdd()
    {
        $itemOccurrence = new FeedItemOccurrence();
        $itemOccurrence->start = new \DateTimeImmutable('2023-01-19T14:00:00+00:00');
        $itemOccurrence->end = new \DateTimeImmutable('2023-01-19T15:30:00+00:00');
        $itemOccurrence->price = '200';

        $itemOccurrence2 = new FeedItemOccurrence();
        $itemOccurrence2->start = new \DateTimeImmutable('2023-02-18T10:00:00+00:00');
        $itemOccurrence2->end = new \DateTimeImmutable('2023-02-18T16:30:00+00:00');
        $itemOccurrence2->price = '100';
        $input = [$itemOccurrence, $itemOccurrence2];

        $eventOccurrence = new Occurrence();
        $eventOccurrence->setStart(new \DateTimeImmutable('2023-01-19T14:00:00+00:00'))
            ->setEnd(new \DateTimeImmutable('2023-01-19T15:30:00+00:00'))
            ->setTicketPriceRange('200');

        $event = new Event();
        $event->addOccurrence($eventOccurrence);

        $factory = $this->getFactory();
        $factory->createOrLookup($input, $event);

        // Check that object is the same and the price has been updated.
        $this->assertEquals($eventOccurrence, $event->getOccurrences()[0]);
        $this->assertCount(count($input), $event->getOccurrences());
        $this->assertEquals($itemOccurrence2->price, $event->getOccurrences()[1]->getTicketPriceRange());
        $this->assertEquals(0, $event->getOccurrences()[1]->getStart()->diff($itemOccurrence2->start)->s);
        $this->assertEquals(0, $event->getOccurrences()[1]->getEnd()->diff($itemOccurrence2->end)->s);
    }

    /**
     * @throws \Exception
     */
    public function testOccurrenceRemove()
    {
        $itemOccurrence = new FeedItemOccurrence();
        $itemOccurrence->start = new \DateTimeImmutable('2023-01-19T14:00:00+00:00');
        $itemOccurrence->end = new \DateTimeImmutable('2023-01-19T15:30:00+00:00');
        $itemOccurrence->price = '200';

        $input = [$itemOccurrence];

        $eventOccurrence = new Occurrence();
        $eventOccurrence->setStart(new \DateTimeImmutable('2023-01-19T14:00:00+00:00'))
            ->setEnd(new \DateTimeImmutable('2023-01-19T15:30:00+00:00'))
            ->setTicketPriceRange('200');
        $eventOccurrence2 = new Occurrence();
        $eventOccurrence2->setStart(new \DateTimeImmutable('2023-05-20T10:00:00+00:00'))
            ->setEnd(new \DateTimeImmutable('2023-05-20T11:30:00+00:00'))
            ->setTicketPriceRange('300-400');

        $event = new Event();
        $event->addOccurrence($eventOccurrence);
        $event->addOccurrence($eventOccurrence2);

        $factory = $this->getFactory();
        $factory->createOrLookup($input, $event);

        // Check that object is the same and the price has been updated.
        $this->assertEquals($eventOccurrence, $event->getOccurrences()[0]);
        $this->assertCount(count($input), $event->getOccurrences());
        $this->assertEquals($itemOccurrence->price, $event->getOccurrences()[0]->getTicketPriceRange());
        $this->assertEquals(0, $event->getOccurrences()[0]->getStart()->diff($itemOccurrence->start)->s);
        $this->assertEquals(0, $event->getOccurrences()[0]->getEnd()->diff($itemOccurrence->end)->s);
    }

    /**
     * Helper function to bootstrap the service tested.
     *
     * @return OccurrencesFactory
     *   The service
     *
     * @throws \Exception
     */
    private function getFactory(): OccurrencesFactory
    {
        self::bootKernel();
        $container = OccurrencesFactoryTest::getContainer();

        return $container->get(OccurrencesFactory::class);
    }
}
