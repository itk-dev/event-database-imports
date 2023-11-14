<?php

namespace App\Service;

use App\Model\DateTimeInterval;

interface TimeInterface
{
    /**
     * Split the time span into 1-day DateTimeInterval value objects.
     *
     * ('2001-01-01 13:00:00', '2001-01-03 10:00:00') -> [
     *    (start: '2001-01-01 13:00:00.00000', end: '2001-01-01 23:59:59.99999'),
     *    (start: '2001-01-02 00:00:00.00000', end: '2001-01-02 23:59:59.99999'),
     *    (start: '2001-01-03 00:00:00.00000', end: '2001-01-03 10:00:00.00000')
     *  ]
     *
     * @param \DateTimeImmutable $start
     *    The start time
     * @param \DateTimeImmutable $end
     *    The end time
     *
     * @return array<DateTimeInterval> array
     *    Array of DateTimeInterval value objects spanning the start/end time
     */
    public function getIntervals(\DateTimeImmutable $start, \DateTimeImmutable $end): array;
}
