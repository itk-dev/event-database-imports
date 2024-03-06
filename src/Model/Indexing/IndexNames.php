<?php

namespace App\Model\Indexing;

enum IndexNames: string
{
    case Events = 'events';
    case Organizations = 'organizations';
    case Occurrences = 'occurrences';
    case DailyOccurrences = 'daily_occurrences';
    case Tags = 'tags';
    case Vocabularies = 'vocabularies';
    case Locations = 'locations';
    // @todo add apikeys index
    // case ApiKeys = 'api_keys';

    public static function values(): array
    {
        return array_column(IndexNames::cases(), 'value');
    }
}
