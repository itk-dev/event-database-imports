<?php

namespace App\Model\Indexing\Mappings;

class Tag implements MappingsInterface
{
    public const array PROPERTIES = [
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
        'slug' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => true,
            'norms' => false,
        ],
        'vocabulary' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => true,
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
