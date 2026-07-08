<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\TimeInterval;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(TimeInterval::class)]
final class TimeIntervalTest extends KernelTestCase
{
    /**
     * @throws \Exception
     */
    public function testTimeIntervalSingleDay(): void
    {
        $time = $this->getTimeService();

        $start = \Carbon\CarbonImmutable::parse('2023-09-23T10:00:00+02:00');
        $end = \Carbon\CarbonImmutable::parse('2023-09-23T12:30:00+02:00');

        $times = $time->getIntervals($start, $end);

        $this->assertCount(1, $times);
        $this->assertEquals($start, $times[0]->start);
        $this->assertEquals($end, $times[0]->end);
    }

    public function testTimeIntervalSpanMidnight(): void
    {
        $time = $this->getTimeService();

        $start = \Carbon\CarbonImmutable::parse('2023-09-23T10:00:00+02:00');
        $end = \Carbon\CarbonImmutable::parse('2023-09-23T12:30:00+02:00');

        $times = $time->getIntervals($start, $end);

        $this->assertCount(1, $times);
        $this->assertEquals($start, $times[0]->start);
        $this->assertEquals($end, $times[0]->end);
    }

    /**
     * @throws \Exception
     */
    public function testTimeIntervalTwoDays(): void
    {
        $time = $this->getTimeService();

        $start = \Carbon\CarbonImmutable::parse('2023-09-29T10:00:00+02:00');
        $end = \Carbon\CarbonImmutable::parse('2023-09-30T12:30:00+02:00');

        $times = $time->getIntervals($start, $end);

        $this->assertCount(2, $times);
        $first = $times[0];
        $second = $times[1];

        $this->assertEquals($start, $first->start);
        $this->assertEquals(\Carbon\CarbonImmutable::parse('2023-09-30T00:00:00+02:00'), $first->end);

        $this->assertEquals(\Carbon\CarbonImmutable::parse('2023-09-30T00:00:00+02:00'), $second->start);
        $this->assertEquals($end, $second->end);
    }

    /**
     * @throws \Exception
     */
    public function testTimeIntervalTwoDaysUTC(): void
    {
        $time = $this->getTimeService();

        $start = \Carbon\CarbonImmutable::parse('2023-09-29T10:00:00+00:00');
        $end = \Carbon\CarbonImmutable::parse('2023-09-30T12:30:00+00:00');

        $times = $time->getIntervals($start, $end);

        $this->assertCount(2, $times);
        $first = $times[0];
        $second = $times[1];

        $this->assertEquals($start, $first->start);
        $this->assertEquals(\Carbon\CarbonImmutable::parse('2023-09-30T00:00:00+02:00'), $first->end);

        $this->assertEquals(\Carbon\CarbonImmutable::parse('2023-09-30T00:00:00+02:00'), $second->start);
        $this->assertEquals($end, $second->end);
    }

    /**
     * @throws \Exception
     */
    public function testTimeIntervalMultiDays(): void
    {
        $time = $this->getTimeService();

        $start = \Carbon\CarbonImmutable::parse('2023-09-23T10:00:00+02:00');
        $end = \Carbon\CarbonImmutable::parse('2023-09-30T12:30:00+02:00');

        $times = $time->getIntervals($start, $end);

        $this->assertCount(8, $times);
        $this->assertEquals($start, reset($times)->start);
        $this->assertEquals($end, end($times)->end);

        // Test date in the middle of the range span the full day.
        $this->assertEquals(\Carbon\CarbonImmutable::parse('2023-09-27T00:00:00+02:00'), $times[4]->start);
        $this->assertEquals(\Carbon\CarbonImmutable::parse('2023-09-28T00:00:00+02:00'), $times[4]->end);
    }

    /**
     * Helper function to bootstrap the service tested.
     *
     * @return TimeInterval
     *   The service
     *
     * @throws \Exception
     */
    private function getTimeService(): TimeInterval
    {
        return new TimeInterval('Europe/Copenhagen');
    }
}
