<?php

namespace App\Service\Indexing;

use App\Model\Indexing\IndexFieldTypes;
use App\Model\Indexing\IndexNames;
use Elastic\Elasticsearch\Client;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Serializer\Context\Normalizer\DateTimeNormalizerContextBuilder;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsTaggedItem(index: IndexNames::Organizations->value, priority: 10)]
final class IndexingOrganizations extends AbstractIndexingElastic
{
    protected const string INDEX_ALIAS = IndexNames::Organizations->value;

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly Client $client,
    ) {
        parent::__construct($this->client);
    }

    public function serialize(IndexItemInterface $item): array
    {
        $contextBuilder = (new ObjectNormalizerContextBuilder())
            ->withGroups([IndexNames::Organizations->value]);
        $contextBuilder = (new DateTimeNormalizerContextBuilder())
            ->withContext($contextBuilder)
            ->withFormat(IndexFieldTypes::DATEFORMAT);

        return $this->serializer->normalize($item, null, $contextBuilder->toArray());
    }
}
