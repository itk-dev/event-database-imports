<?php

namespace App\Model\Indexing;

enum IndexNames: string
{
    case Events = 'events';
    case DailyOccurrences = 'daily';

    public static function values(): array
    {
        return array_column(IndexNames::cases(), 'value');
    }
}
