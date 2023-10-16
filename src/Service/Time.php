<?php

namespace App\Service;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

final class Time
{
    public function __construct(
    ) {
    }

    /**
     * Split the state and end DateTime objects into 1-day intervals.
     *
     * @param \DateTimeImmutable $start
     *    The start time
     * @param \DateTimeImmutable $end
     *    The end time
     *
     * @return array<\DateTimeImmutable> array
     *   Array with DateTimeImmutable with the first element is the start date and the
     */
    public function getInterval(\DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        $periods = (new CarbonPeriod($start, '1 day', $end))->toArray();

        // Carbon period returns the last element with the time part on the last element set to the first elements time.
        // But that not what we want here, so we find the different in seconds and that to the last element.
        $lastPeriod = end($periods);
        $seconds = $this->getDiffInSeconds($lastPeriod->toDateTimeImmutable(), $end);
        $periods[\count($periods) - 1] = new Carbon($lastPeriod->addSeconds($seconds)->toDate());

        return array_map(fn ($item) => $item->toDateTimeImmutable(), $periods);
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
        return intval((new Carbon($start))->diffAsCarbonInterval(new Carbon($end))->totalSeconds);
    }
}
