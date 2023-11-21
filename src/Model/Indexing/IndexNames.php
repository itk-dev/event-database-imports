<?php

namespace App\Model\Indexing;

enum IndexNames: string
{
    case Events = 'events';
    case Organization = 'organization';

    public static function values(): array
    {
        return array_column(IndexNames::cases(), 'value');
    }
}
