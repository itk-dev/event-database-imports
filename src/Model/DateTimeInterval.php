<?php

namespace App\Model;

final class DateTimeInterval
{
    public function __construct(
        public \DateTimeImmutable $start,
        public \DateTimeImmutable $end,
    ) {
    }
}
