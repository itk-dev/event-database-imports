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
use Symfony\Component\Serializer\Context\Normalizer\DateTimeNormalizerContextBuilder;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsTaggedItem(index: IndexNames::Organization->value, priority: 10)]
final class IndexingOrganization extends AbstractIndexingElastic
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly string $indexAliasName,
        private readonly Client $client,
    ) {
        parent::__construct($this->indexAliasName, $this->client);
    }

    public function serialize(IndexItemInterface $item): array
    {
        $contextBuilder = (new ObjectNormalizerContextBuilder())
            ->withGroups([IndexNames::Organization->value]);
        $contextBuilder = (new DateTimeNormalizerContextBuilder())
            ->withContext($contextBuilder)
            ->withFormat(IndexFieldTypes::DATEFORMAT);

        return $this->serializer->normalize($item, null, $contextBuilder->toArray());
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
                ],
            ]);

            if (!in_array($response->getStatusCode(), [Response::HTTP_OK, Response::HTTP_NO_CONTENT])) {
                throw new IndexingException('Unable to create new index', $response->getStatusCode());
            }
        } catch (ClientResponseException|MissingParameterException|ServerResponseException $e) {
            throw new IndexingException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
