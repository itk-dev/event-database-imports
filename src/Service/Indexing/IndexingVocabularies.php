<?php

namespace App\Service\Indexing;

use App\Model\Indexing\IndexFieldTypes;
use App\Model\Indexing\IndexNames;
use Elastic\Elasticsearch\Client;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Serializer\Context\Normalizer\DateTimeNormalizerContextBuilder;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsTaggedItem(index: IndexNames::Vocabularies->value, priority: 10)]
final class IndexingVocabularies extends AbstractIndexingElastic
{
    protected const string INDEX_ALIAS = IndexNames::Vocabularies->value;

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly Client $client,
    ) {
        parent::__construct($this->client);
    }

    public function serialize(IndexItemInterface $item): array
    {
        $contextBuilder = (new ObjectNormalizerContextBuilder())
            ->withGroups([IndexNames::Vocabularies->value]);
        $contextBuilder = (new DateTimeNormalizerContextBuilder())
            ->withContext($contextBuilder)
            ->withFormat(IndexFieldTypes::DATEFORMAT);

        $data = $this->serializer->normalize($item, null, $contextBuilder->toArray());

        // @todo: Figure out how to do these changes with the serializer. This is just....
        // Get tag names.
        $data['tags'] = array_column($data['tags'], 'name');

        return $data;
    }
}
