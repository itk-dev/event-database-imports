<?php

namespace App\Model\Feed;

final readonly class FeedConfiguration
{
    public function __construct(
        public string $type,
        public string $url,
        public string $base,
        public string $timezone,
        public string $rootPointer,
        /** @var non-empty-string */
        public string $dateFormat,
        /** @var array list<string> */
        public array $mapping = [],
        /** @var array list<string> */
        public array $defaults = [],
        public array $clientHeaders = [],
        public ?FeedPagination $pagination = null,
    ) {
    }

    public function supportsPagination(): bool
    {
        return !(null === $this->pagination) && $this->pagination->supportsPagination();
    }

    public static function getConfigurationTemplate(): array
    {
        return [
            'type' => 'json',
            'url' => '',
            'base' => '',
            'timezone' => 'Europe/Copenhagen',
            'rootPointer' => '/-',
            'dateFormat' => "Y-m-d\TH:i:sP",
            'pagination' => [
                'pageParameter' => '',
                'limitParameter' => '',
            ],
            'mapping' => [
                'id' => 'id',
                'title' => 'title',
                'excerpt' => 'excerpt',
                'description' => 'description',
                'url' => 'url',
                'image' => 'image',
                'occurrences.*.start' => 'occurrences.*.start',
                'occurrences.*.end' => 'occurrences.*.end',
                'price' => 'occurrences.*.price',
            ],
            'defaults' => [
                'publicAccess' => true,
                'location' => [
                    'name' => '',
                    'country' => '',
                    'city' => '',
                    'postalCode' => null,
                    'street' => '',
                    'region' => '',
                    'url' => '',
                    'telephone' => '',
                    'mail' => '',
                    'disabilityAccess' => true,
                ],
                'organization' => [
                    'name' => '',
                    'email' => '',
                    'url' => '',
                ],
            ],
        ];
    }
}
