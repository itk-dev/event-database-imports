<?php

namespace App\Service;

use App\Model\DateTimeInterval;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriodImmutable;

final class Time
{
    public function __construct(
    ) {
    }

    /**
     * Split the time span into 1-day DateTimeInterval value objects.
     *
     * @param \DateTimeImmutable $start
     *    The start time
     * @param \DateTimeImmutable $end
     *    The end time
     *
     * @return array<DateTimeInterval> array
     *    Array of DateTimeInterval value objects spanning the start/end time
     */
    public function getInterval(\DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        $periods = (new CarbonPeriodImmutable($start, '1 day', $end))->toArray();

        // If start and end is within the same day, simple fast track a return those dates.
        if (1 === count($periods)) {
            return [new DateTimeInterval(start: $start, end: $end)];
        }

        // Carbon period returns the last element with the time part on the last element set to the first elements time.
        // But that not what we want here, so we find the different in seconds and that to the last element.
        $lastPeriod = end($periods);
        $seconds = $this->getDiffInSeconds($lastPeriod->toDateTimeImmutable(), $end);
        $periods[\count($periods) - 1] = new CarbonImmutable($lastPeriod->addSeconds($seconds)->toDate());

        return $this->CovertToTimeDateIntervals($periods);
    }

    /**
     * Helper function to convert array of dates into date time value objects.
     *
     * @param array<CarbonInterface> $periods
     *
     * @return array<DateTimeInterval>
     */
    private function CovertToTimeDateIntervals(array $periods): array
    {
        $output = [];

        // First element has a defined start date.
        $first = array_shift($periods);
        $output[] = new DateTimeInterval(start: $first->toDateTimeImmutable(), end: $first->endOfDay()->toDateTimeImmutable());

        $lastKey = array_key_last($periods);
        foreach ($periods as $key => $period) {
            if ($lastKey === $key) {
                // Set end time interval.
                $output[] = new DateTimeInterval(start: $period->startOfDay()->toDateTimeImmutable(), end: $period->toDateTimeImmutable());
            } else {
                // All the days between the first and last element.
                $output[] = new DateTimeInterval(start: $period->startOfDay()->toDateTimeImmutable(), end: $period->endOfDay()->toDateTimeImmutable());
            }
        }

        return $output;
    }

    /**
     * Find difference in seconds between to DateTime objects.
     *
     * @param \DateTimeImmutable $start
     *   The start time
     * @param \DateTimeImmutable $end
     *   The end time
     *
     * @return int
     *   The different in whole seconds between the two DateTime objects
     */
    private function getDiffInSeconds(\DateTimeImmutable $start, \DateTimeImmutable $end): int
    {
        return intval((new CarbonImmutable($start))->diffAsCarbonInterval(new CarbonImmutable($end))->totalSeconds);
    }
}
