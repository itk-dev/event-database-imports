<?php

namespace App\Service\Indexing;

use App\Exception\IndexingException;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Symfony\Component\HttpFoundation\Response;

final class IndexingEvents extends AbstractIndexingElastic
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
                'format' => 'yyyy-MM-dd HH:mm:ss',
                'index' => false,
                'doc_values' => true,
            ],
            'updated' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd HH:mm:ss',
                'index' => false,
                'doc_values' => true,
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
