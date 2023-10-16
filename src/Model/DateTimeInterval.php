<?php

namespace App\Model;

final class DateTimeInterval
{
    public function __construct(
        public ?\DateTimeImmutable $start = null,
        public ?\DateTimeImmutable $end = null,
    ) {
    }
}
