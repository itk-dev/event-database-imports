<?php

namespace App\Model\Indexing\Mappings;

use App\Model\Indexing\IndexFieldTypes;

class Event implements MappingsInterface
{
    public const array PROPERTIES = [
        'entityId' => [
            'type' => 'integer',
            'doc_values' => false,
        ],
        'title' => [
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
        'excerpt' => [
            'type' => 'text',
            'index_options' => 'docs',
            'index' => false,
            'norms' => false,
        ],
        'description' => [
            'type' => 'text',
            'index_options' => 'docs',
            'index' => true,
            'norms' => false,
        ],
        'url' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => false,
            'doc_values' => false,
            'norms' => false,
        ],
        'ticketUrl' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => false,
            'doc_values' => false,
            'norms' => false,
        ],
        'imageUrls' => [
            'properties' => [
                'small' => [
                    'type' => 'keyword',
                    'index_options' => 'docs',
                    'index' => false,
                    'doc_values' => false,
                    'norms' => false,
                ],
                'medium' => [
                    'type' => 'keyword',
                    'index_options' => 'docs',
                    'index' => false,
                    'doc_values' => false,
                    'norms' => false,
                ],
                'large' => [
                    'type' => 'keyword',
                    'index_options' => 'docs',
                    'index' => false,
                    'doc_values' => false,
                    'norms' => false,
                ],
            ],
        ],
        'publicAccess' => [
            'type' => 'boolean',
            'index' => true,
            'doc_values' => false,
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
        'tags' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => true,
            'doc_values' => false,
            'norms' => false,
        ],
        'organizer' => ['properties' => Organizer::PROPERTIES],
        'partners' => ['properties' => Organizer::PROPERTIES],
        'location' => ['properties' => Location::PROPERTIES],
    ];

    #[\Override]
    public static function getProperties(): array
    {
        return self::PROPERTIES;
    }
}
