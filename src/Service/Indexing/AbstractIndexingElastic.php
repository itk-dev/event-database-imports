<?php

namespace App\Service\Indexing;

use App\Exception\IndexingException;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractIndexingElastic implements IndexingInterface
{
    private ?string $newIndexName = null;

    public function __construct(
        private readonly string $indexAliasName,
        private readonly Client $client,
    ) {
    }

    public function index(IndexItemInterface $item): void
    {
        /** @var IndexItemInterface $item */
        $params = [
            'index' => $this->indexAliasName,
            'id' => $item->getId(),
            'body' => $item->toArray(),
        ];

        try {
            /** @var Elasticsearch $response */
            $response = $this->client->index($params);

            if (Response::HTTP_OK !== $response->getStatusCode() && Response::HTTP_CREATED !== $response->getStatusCode() && Response::HTTP_NO_CONTENT !== $response->getStatusCode()) {
                throw new IndexingException('Unable to add item to index', $response->getStatusCode());
            }
        } catch (ClientResponseException|MissingParameterException|ServerResponseException $e) {
            throw new IndexingException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public function delete(int $id): void
    {
        $params = [
            'index' => $this->indexAliasName,
            'id' => $id,
        ];

        try {
            /** @var Elasticsearch $response */
            $response = $this->client->delete($params);

            if (Response::HTTP_OK !== $response->getStatusCode() && Response::HTTP_ACCEPTED !== $response->getStatusCode() && Response::HTTP_NO_CONTENT !== $response->getStatusCode()) {
                throw new IndexingException('Unable to delete item from index', $response->getStatusCode());
            }
        } catch (ClientResponseException|MissingParameterException|ServerResponseException $e) {
            throw new IndexingException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function bulk(array $items): void
    {
        try {
            if (null === $this->newIndexName) {
                $this->newIndexName = $this->indexAliasName.'_'.date('Y-m-d-His');
                $this->createEsIndex($this->newIndexName);
            }

            $params = [];
            foreach ($items as $item) {
                /* @var IndexItemInterface $item */
                $params['body'][] = [
                    'index' => [
                        '_index' => $this->newIndexName,
                        '_id' => $item->getId(),
                    ],
                ];

                $params['body'][] = $item->toArray();
            }

            $this->client->bulk($params);
        } catch (ClientResponseException|ServerResponseException $e) {
            throw new IndexingException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function createIndex(): void
    {
        if ($this->indexExists()) {
            throw new IndexingException('Index already exists');
        }

        $newIndexName = $this->indexAliasName.'_'.date('Y-m-d-His');
        $this->createEsIndex($newIndexName);
        $this->refreshIndex($newIndexName);

        try {
            $this->client->indices()->updateAliases([
                'body' => [
                    'actions' => [
                        [
                            'add' => [
                                'index' => $newIndexName,
                                'alias' => $this->indexAliasName,
                            ],
                        ],
                    ],
                ],
            ]);
        } catch (ClientResponseException|ServerResponseException $e) {
            throw new IndexingException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function indexExists(): bool
    {
        try {
            /** @var Elasticsearch $response */
            $response = $this->client->indices()->getAlias(['name' => $this->indexAliasName]);

            return Response::HTTP_OK === $response->getStatusCode();
        } catch (ClientResponseException|ServerResponseException $e) {
            if (Response::HTTP_NOT_FOUND === $e->getCode()) {
                return false;
            }

            throw new IndexingException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Switch new index with old by updating alias.
     *
     * @throws IndexingException
     */
    public function switchIndex(): void
    {
        if (null === $this->newIndexName) {
            throw new IndexingException('New index name cannot be null');
        }

        try {
            $existingIndexName = $this->getCurrentActiveIndexName();
            $this->refreshIndex($this->newIndexName);

            $this->client->indices()->updateAliases([
                'body' => [
                    'actions' => [
                        [
                            'add' => [
                                'index' => $this->newIndexName,
                                'alias' => $this->indexAliasName,
                            ],
                        ],
                    ],
                ],
            ]);
            $this->client->indices()->delete(['index' => $existingIndexName]);
        } catch (ClientResponseException|MissingParameterException|ServerResponseException $e) {
            throw new IndexingException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Refresh index to ensure data is searchable.
     *
     * @param string $indexName
     *   Name of the index to refresh
     *
     * @throws IndexingException
     */
    private function refreshIndex(string $indexName): void
    {
        try {
            $this->client->indices()->refresh(['index' => $indexName]);
        } catch (ClientResponseException|ServerResponseException $e) {
            throw new IndexingException('Unable to refresh index', (int) $e->getCode(), $e);
        }
    }

    /**
     * Get the current active index name base on alias.
     *
     * @return string
     *   The name of the active index
     *
     * @throws IndexingException
     */
    private function getCurrentActiveIndexName(): string
    {
        try {
            /** @var Elasticsearch $response */
            $response = $this->client->indices()->getAlias(['name' => $this->indexAliasName]);

            if (Response::HTTP_OK !== $response->getStatusCode()) {
                throw new IndexingException('Unable to get aliases', $response->getStatusCode());
            }

            $aliases = $response->asArray();
            $aliases = array_keys($aliases);

            return array_pop($aliases);
        } catch (ClientResponseException|ServerResponseException $e) {
            throw new IndexingException($e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    /**
     * Create new index.
     *
     * Index optimizations
     *
     * @see https://www.inventaconsulting.net/post/a-guide-to-optimizing-elasticsearch-mappings
     *
     * 'dynamic' => 'strict'
     *
     * If new fields are detected, an exception is thrown and the document is rejected.
     * New fields must be explicitly added to the mapping.
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/dynamic.html#dynamic-parameters
     *
     * 'index_options' => 'docs'
     *
     * The index_options parameter controls what information is added to the
     * inverted index for search and highlighting purposes.
     * 'docs': Only the doc number is indexed. Can answer the question
     * Does this term exist in this field?
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/8.5/index-options.html
     *
     * 'doc_values' => false
     *
     * If you are sure that you don’t need to sort or aggregate on a field, or access the
     * field value from a script, you can disable doc values in order to save disk space
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/8.5/doc-values.html#_disabling_doc_values
     *
     * 'norms' => false
     *
     * Although useful for scoring, norms also require quite a lot of disk (typically in the
     * order of one byte per document per field in your index, even for documents that don’t
     * have this specific field). As a consequence, if you don’t need scoring on a specific
     * field, you should disable norms on that field.
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/8.5/norms.html
     *
     * @param string $indexName
     *   Name of the index to create
     *
     * @throws IndexingException
     */
    protected function createEsIndex(string $indexName): void
    {
        throw new IndexingException('Base elastic indexing class do not implement create index');
    }
}
