<?php

namespace App\Model\Indexing\Mappings;

class Vocabularies implements MappingsInterface
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
            'doc_values' => true,
            'norms' => false,
        ],
        'description' => [
            'type' => 'text',
            'index_options' => 'docs',
            'index' => false,
            'norms' => false,
        ],
        'tags' => [
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
