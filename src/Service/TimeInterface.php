<?php

namespace App\Service;

use App\Model\DateTimeInterval;

interface TimeInterface
{
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
    public function getInterval(\DateTimeImmutable $start, \DateTimeImmutable $end): array;
}
