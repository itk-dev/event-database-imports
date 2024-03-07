<?php

namespace App\Service\Indexing;

use App\Exception\IndexingException;
use App\Model\Indexing\IndexNames;
use App\Model\Indexing\Mappings\EventWithOccurrences;
use App\Model\Indexing\Mappings\Location;
use App\Model\Indexing\Mappings\OccurrenceWithEvent;
use App\Model\Indexing\Mappings\Organizer;
use App\Model\Indexing\Mappings\Tag;
use App\Model\Indexing\Mappings\Vocabularies;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Elasticsearch\Helper\Iterators\SearchHitIterator;
use Elastic\Elasticsearch\Helper\Iterators\SearchResponseIterator;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractIndexingElastic implements IndexingInterface
{
    /**
     * Subclasses should override this value.
     */
    protected const string INDEX_ALIAS = 'abstract_error';
    private ?string $newIndexName = null;

    public function __construct(
        private readonly Client $client,
    ) {
    }

    #[\Override]
    public function index(IndexItemInterface $item): void
    {
        $params = [
            'index' => $this::INDEX_ALIAS,
            'id' => $item->getId(),
            'body' => $this->serialize($item),
        ];

        try {
            /** @var Elasticsearch $response */
            $response = $this->client->index($params);

            if (!in_array($response->getStatusCode(), [Response::HTTP_OK, Response::HTTP_CREATED, Response::HTTP_NO_CONTENT])) {
                throw new IndexingException('Unable to add item to index', $response->getStatusCode());
            }
        } catch (ClientResponseException|MissingParameterException|ServerResponseException $e) {
            throw new IndexingException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[\Override]
    public function delete(int $id): void
    {
        $params = [
            'index' => $this::INDEX_ALIAS,
            'id' => $id,
        ];

        try {
            /** @var Elasticsearch $response */
            $response = $this->client->delete($params);

            if (!in_array($response->getStatusCode(), [Response::HTTP_OK, Response::HTTP_ACCEPTED, Response::HTTP_NO_CONTENT])) {
                throw new IndexingException('Unable to delete item from index', $response->getStatusCode());
            }
        } catch (ClientResponseException|MissingParameterException|ServerResponseException $e) {
            throw new IndexingException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[\Override]
    public function bulk(array $items): void
    {
        try {
            if (null === $this->newIndexName) {
                $this->newIndexName = $this::INDEX_ALIAS.'_'.date('Y-m-d-His');
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
                $params['body'][] = $this->serialize($item);
            }

            $response = $this->client->bulk($params);
            if (!in_array($response->getStatusCode(), [Response::HTTP_OK, Response::HTTP_CREATED, Response::HTTP_NO_CONTENT])) {
                throw new IndexingException('Unable to add item to index', $response->getStatusCode());
            }
        } catch (ClientResponseException|ServerResponseException $e) {
            throw new IndexingException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[\Override]
    public function createIndex(): void
    {
        if ($this->indexExists()) {
            throw new IndexingException('Index already exists');
        }

        $newIndexName = $this::INDEX_ALIAS.'_'.date('Y-m-d-His');
        $this->createEsIndex($newIndexName);
        $this->refreshIndex($newIndexName);

        try {
            $this->client->indices()->updateAliases([
                'body' => [
                    'actions' => [
                        [
                            'add' => [
                                'index' => $newIndexName,
                                'alias' => $this::INDEX_ALIAS,
                            ],
                        ],
                    ],
                ],
            ]);
        } catch (ClientResponseException|ServerResponseException $e) {
            throw new IndexingException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[\Override]
    public function dumpIndex(): \Generator
    {
        $params = [
            'index' => $this::INDEX_ALIAS,
            'scroll' => '5m',
            'size' => 100,
            'body' => [
                'query' => [
                    'query_string' => [
                        'query' => '*',
                    ],
                ],
            ],
        ];

        $pages = new SearchResponseIterator($this->client, $params);
        $hits = new SearchHitIterator($pages);

        foreach ($hits as $hit) {
            yield $hit['_source'];
        }
    }

    #[\Override]
    public function indexExists(): bool
    {
        try {
            /** @var Elasticsearch $response */
            $response = $this->client->indices()->getAlias(['name' => $this::INDEX_ALIAS]);

            return Response::HTTP_OK === $response->getStatusCode();
        } catch (ClientResponseException|ServerResponseException $e) {
            if (Response::HTTP_NOT_FOUND === $e->getCode()) {
                return false;
            }

            throw new IndexingException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Switch new index with old by updating alias.
     *
     * @throws IndexingException
     */
    #[\Override]
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
                                'alias' => $this::INDEX_ALIAS,
                            ],
                        ],
                    ],
                ],
            ]);
            $this->client->indices()->delete(['index' => $existingIndexName]);
        } catch (ClientResponseException|MissingParameterException|ServerResponseException $e) {
            throw new IndexingException($e->getMessage(), $e->getCode(), $e);
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
            throw new IndexingException('Unable to refresh index', $e->getCode(), $e);
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
            $response = $this->client->indices()->getAlias(['name' => $this::INDEX_ALIAS]);

            if (Response::HTTP_OK !== $response->getStatusCode()) {
                throw new IndexingException('Unable to get aliases', $response->getStatusCode());
            }

            $aliases = $response->asArray();
            $aliases = array_keys($aliases);

            return array_pop($aliases);
        } catch (ClientResponseException|ServerResponseException $e) {
            throw new IndexingException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get common configuration that every index should use.
     *
     * @param string $indexName
     *    Name of the index to create
     *
     * @return array
     *   Basic/shared configuration between all indexes
     */
    protected function getCommonIndexConfig(string $indexName): array
    {
        return [
            'index' => $indexName,
            'body' => [
                'settings' => [
                    'number_of_shards' => 5,
                    'number_of_replicas' => 0,
                ],
                'mappings' => [
                    // @see https://www.elastic.co/guide/en/elasticsearch/reference/current/dynamic.html#dynamic-parameters
                    'dynamic' => 'strict',
                    'properties' => [],
                ],
            ],
        ];
    }

    /**
     * Healer to convert entities to serialized data that the indexer understands.
     *
     * @throws IndexingException
     */
    #[\Override]
    public function serialize(IndexItemInterface $item): array
    {
        throw new IndexingException('Base elastic indexing class do not implement create index');
    }

    /**
     * Create new index.
     *
     * Index optimizations
     *
     * 'index_options' => 'docs'
     *
     * The index_options parameter controls what information is added to the
     * inverted index for search and highlighting purposes.
     * 'docs': Only the doc number is indexed. Can answer the question
     * Does this term exist in this field?
     *
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
                        'properties' => $this->getIndexProperties(),
                    ],
                ],
            ]);

            if (!in_array($response->getStatusCode(), [Response::HTTP_OK, Response::HTTP_NO_CONTENT])) {
                throw new IndexingException('Unable to create new index: '.$this::INDEX_ALIAS, $response->getStatusCode());
            }
        } catch (ClientResponseException|MissingParameterException|ServerResponseException $e) {
            throw new IndexingException($e->getMessage(), $e->getCode(), $e);
        }
    }

    #[\Override]
    public function criteria(): array
    {
        return [];
    }

    /**
     * @throws IndexingException
     */
    private function getIndexProperties(): array
    {
        $index = IndexNames::from($this::INDEX_ALIAS);

        return match ($index) {
            IndexNames::Organizations => Organizer::getProperties(),
            IndexNames::Events => EventWithOccurrences::getProperties(),
            IndexNames::Locations => Location::getProperties(),
            IndexNames::Tags => Tag::getProperties(),
            IndexNames::Vocabularies => Vocabularies::getProperties(),
            IndexNames::Occurrences, IndexNames::DailyOccurrences => OccurrenceWithEvent::getProperties(),
            // IndexNames::ApiKeys => throw new \Exception('To be implemented'),
        };
    }
}
