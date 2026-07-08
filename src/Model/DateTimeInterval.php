<?php

declare(strict_types=1);

namespace App\Model;

final class DateTimeInterval
{
    public function __construct(
        public \DateTimeImmutable $start,
        public \DateTimeImmutable $end,
    ) {
    }
}
