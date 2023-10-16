<?php

declare(strict_types=1);

namespace App\Tests;

use App\Service\Time;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(Time::class)]
final class TimeServiceTest extends KernelTestCase
{
    /**
     * @throws \Exception
     */
    public function testTimeIntervalSingleDay()
    {
        $time = $this->getTimeService();

        $start = new \DateTimeImmutable('2023-09-23T10:00:00+02:00');
        $end = new \DateTimeImmutable('2023-09-23T12:30:00+02:00');

        $times = $time->getInterval($start, $end);

        $this->assertCount(1, $times);
        $this->assertEquals($start, $times[0]->start);
        $this->assertEquals($end, $times[0]->end);
    }

    /**
     * @throws \Exception
     */
    public function testTimeIntervalTwoDays()
    {
        $time = $this->getTimeService();

        $start = new \DateTimeImmutable('2023-09-29T10:00:00+02:00');
        $end = new \DateTimeImmutable('2023-09-30T12:30:00+02:00');

        $times = $time->getInterval($start, $end);

        $this->assertCount(2, $times);
        $this->assertEquals($start, reset($times)->start);
        $this->assertEquals(new \DateTimeImmutable('2023-09-29T23:59:59.999999+02:00'), reset($times)->end);
        $this->assertEquals(new \DateTimeImmutable('2023-09-30T00:00:00+02:00'), end($times)->start);
        $this->assertEquals($end, end($times)->end);
    }

    /**
     * @throws \Exception
     */
    public function testTimeIntervalMultiDays()
    {
        $time = $this->getTimeService();

        $start = new \DateTimeImmutable('2023-09-23T10:00:00+02:00');
        $end = new \DateTimeImmutable('2023-09-30T12:30:00+02:00');

        $times = $time->getInterval($start, $end);

        $this->assertCount(8, $times);
        $this->assertEquals($start, reset($times)->start);
        $this->assertEquals($end, end($times)->end);

        // Test date in the middle of the range span the full day.
        $this->assertEquals(new \DateTimeImmutable('2023-09-27T00:00:00+02:00'), $times[4]->start);
        $this->assertEquals(new \DateTimeImmutable('2023-09-27T23:59:59.999999+02:00'), $times[4]->end);
    }

    /**
     * Helper function to bootstrap the service tested.
     *
     * @return Time
     *   The service
     *
     * @throws \Exception
     */
    private function getTimeService(): Time
    {
        self::bootKernel();
        $container = TimeServiceTest::getContainer();

        return $container->get(Time::class);
    }
}
