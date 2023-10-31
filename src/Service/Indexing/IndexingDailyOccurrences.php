<?php

namespace App\Service\Indexing;

use App\Exception\IndexingException;
use App\Model\Indexing\IndexNames;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpFoundation\Response;

#[AsTaggedItem(index: IndexNames::DailyOccurrences->value, priority: 10)]
final class IndexingDailyOccurrences extends AbstractIndexingElastic
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
                            'entityId' => [
                                'type' => 'integer',
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
            throw new IndexingException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
