<?php

namespace App\Model\Indexing\Mappings;

class Location implements MappingsInterface
{
    public const array PROPERTIES = [
        'entityId' => [
            'type' => 'integer',
            'doc_values' => false,
        ],
        'name' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => true,
            'doc_values' => false,
            'norms' => false,
        ],
        'image' => [
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
        'telephone' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => false,
            'doc_values' => false,
            'norms' => false,
        ],
        'disabilityAccess' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => false,
            'doc_values' => false,
            'norms' => false,
        ],
        'mail' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => false,
            'doc_values' => false,
            'norms' => false,
        ],
        'city' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => false,
            'doc_values' => false,
            'norms' => false,
        ],
        'street' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => false,
            'doc_values' => false,
            'norms' => false,
        ],
        'suite' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => false,
            'doc_values' => false,
            'norms' => false,
        ],
        'region' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => false,
            'doc_values' => false,
            'norms' => false,
        ],
        'postalCode' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => true,
            'doc_values' => false,
            'norms' => false,
        ],
        'country' => [
            'type' => 'keyword',
            'index_options' => 'docs',
            'index' => false,
            'doc_values' => false,
            'norms' => false,
        ],
        'coordinates' => [
            'type' => 'geo_point',
        ],
    ];

    #[\Override]
    public static function getProperties(): array
    {
        return self::PROPERTIES;
    }
}
