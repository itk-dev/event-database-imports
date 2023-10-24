<?php

namespace App\Service\Indexing;

use App\Exception\IndexingException;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Symfony\Component\HttpFoundation\Response;

final class IndexingDailyOccurrences extends AbstractIndexingElastic
{
    private ?string $newIndexName = null;

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
        try {
            /** @var Elasticsearch $response */
            $response = $this->client->indices()->create([
                'index' => $indexName,
                'body' => [
                    'settings' => [
                        'number_of_shards' => 5,
                        'number_of_replicas' => 0,
                    ],
                    'mappings' => [
                        'dynamic' => 'strict',
                        'properties' => [
                            'isType' => [
                                'type' => 'keyword',
                                'index_options' => 'docs',
                                'doc_values' => false,
                                'norms' => false,
                            ],
                            'isIdentifier' => [
                                'type' => 'keyword',
                                'index_options' => 'docs',
                                // API responses are sorted by identifier
                                'doc_values' => true,
                                'norms' => false,
                            ],
                            'imageFormat' => [
                                'type' => 'keyword',
                                'index_options' => 'docs',
                                'index' => false,
                                'doc_values' => false,
                                'norms' => false,
                            ],
                            'imageUrl' => [
                                'type' => 'text',
                                'index' => false,
                                'norms' => false,
                            ],
                            'width' => [
                                'type' => 'integer',
                                'index' => false,
                                'doc_values' => false,
                            ],
                            'height' => [
                                'type' => 'integer',
                                'doc_values' => false,
                            ],
                            'generic' => [
                                'type' => 'boolean',
                                'doc_values' => false,
                            ],
                        ],
                    ],
                ],
            ]);

            if (Response::HTTP_OK !== $response->getStatusCode() && Response::HTTP_NO_CONTENT !== $response->getStatusCode()) {
                throw new IndexingException('Unable to create new index', $response->getStatusCode());
            }
        } catch (ClientResponseException|MissingParameterException|ServerResponseException $e) {
            throw new IndexingException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }
}
