<?php

namespace App\Model\Indexing\Mappings;

use App\Model\Indexing\IndexFieldTypes;

class Organizer implements MappingsInterface
{
    public const array PROPERTIES = [
        'entityId' => [
            'type' => 'integer',
            'doc_values' => false,
        ],
        'name' => [
            'type' => 'text',
            'analyzer' => 'standard',
            'search_analyzer' => 'standard',
            'fields' => [
                'keyword' => [
                    'type' => 'keyword',
                ],
            ],
            'index_options' => 'docs',
            'index' => true,
            'norms' => false,
            'fielddata' => true,
        ],
        'email' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => false,
            'doc_values' => false,
            'norms' => false,
        ],
        'url' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => false,
            'doc_values' => false,
            'norms' => false,
        ],
        'created' => [
            'type' => 'date',
            'format' => IndexFieldTypes::DATEFORMAT_ES,
            'index' => false,
            'doc_values' => true,
        ],
        'updated' => [
            'type' => 'date',
            'format' => IndexFieldTypes::DATEFORMAT_ES,
            'index' => false,
            'doc_values' => true,
        ],
    ];

    #[\Override]
    public static function getProperties(): array
    {
        return self::PROPERTIES;
    }
}
