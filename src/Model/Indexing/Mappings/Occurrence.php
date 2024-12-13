<?php

namespace App\Model\Indexing\Mappings;

use App\Model\Indexing\IndexFieldTypes;

class Occurrence implements MappingsInterface
{
    public const array PROPERTIES = [
        'entityId' => [
            'type' => 'integer',
            'doc_values' => false,
        ],
        'start' => [
            'type' => 'date',
            'format' => IndexFieldTypes::DATEFORMAT_ES,
            'index' => false,
            'doc_values' => true,
        ],
        'end' => [
            'type' => 'date',
            'format' => IndexFieldTypes::DATEFORMAT_ES,
            'index' => false,
            'doc_values' => true,
        ],
        'ticketPriceRange' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => false,
            'doc_values' => false,
            'norms' => false,
        ],
        'room' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => false,
            'doc_values' => false,
            'norms' => false,
        ],
        'status' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => false,
            'doc_values' => false,
            'norms' => false,
        ],
    ];

    #[\Override]
    public static function getProperties(): array
    {
        return self::PROPERTIES;
    }
}
