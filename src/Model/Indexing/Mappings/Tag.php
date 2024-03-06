<?php

namespace App\Model\Indexing\Mappings;

class Tag implements MappingsInterface
{
    public const array PROPERTIES = [
        'name' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => true,
            'norms' => false,
        ],
        'vocabularies' => [
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
