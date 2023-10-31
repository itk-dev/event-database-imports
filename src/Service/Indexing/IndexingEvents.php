<?php

namespace App\Service\Indexing;

use App\Exception\IndexingException;
use App\Model\Indexing\IndexFieldTypes;
use App\Model\Indexing\IndexNames;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpFoundation\Response;

#[AsTaggedItem(index: IndexNames::Events->value, priority: 10)]
final class IndexingEvents extends AbstractIndexingElastic
{
    public function __construct(
        private readonly string $indexAliasName,
        private readonly Client $client,
    ) {
        parent::__construct($this->indexAliasName, $this->client);
    }

    /**
     * @throws IndexingException
     */
    protected function createEsIndex(string $indexName): void
    {
        $configuration = $this->getCommonIndexConfig($indexName);
        $configuration['body']['mappings']['properties'] = [
            'entityId' => [
                'type' => 'integer',
                'doc_values' => false,
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
                'index' => false,
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
            'imageUrl' => [
                'type' => 'keyword',
                'index_options' => 'docs',
                'index' => false,
                'doc_values' => false,
                'norms' => false,
            ],
            'public' => [
                'type' => 'boolean',
                'index' => false,
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
                'index' => false,
                'doc_values' => false,
                'norms' => false,
            ],
            'organizer' => [
                'properties' => [
                    'entityId' => [
                        'type' => 'integer',
                        'doc_values' => false,
                    ],
                    'name' => [
                        'type' => 'keyword',
                        'index_options' => 'docs',
                        'index' => false,
                        'doc_values' => false,
                        'norms' => false,
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
                ],
            ],
            'location' => [
                'properties' => [
                    'entityId' => [
                        'type' => 'integer',
                        'doc_values' => false,
                    ],
                    'name' => [
                        'type' => 'keyword',
                        'index_options' => 'docs',
                        'index' => false,
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
                        'index' => false,
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
                ],
            ],
        ];

        try {
            /** @var Elasticsearch $response */
            $response = $this->client->indices()->create($configuration);

            if (Response::HTTP_OK !== $response->getStatusCode() && Response::HTTP_NO_CONTENT !== $response->getStatusCode()) {
                throw new IndexingException('Unable to create new index', $response->getStatusCode());
            }
        } catch (ClientResponseException|MissingParameterException|ServerResponseException $e) {
            throw new IndexingException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
