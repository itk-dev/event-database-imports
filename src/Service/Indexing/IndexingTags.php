<?php

namespace App\Service\Indexing;

use App\Model\Indexing\IndexFieldTypes;
use App\Model\Indexing\IndexNames;
use Elastic\Elasticsearch\Client;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Serializer\Context\Normalizer\DateTimeNormalizerContextBuilder;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsTaggedItem(index: IndexNames::Tags->value, priority: 10)]
final class IndexingTags extends AbstractIndexingElastic
{
    protected const string INDEX_ALIAS = IndexNames::Tags->value;

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly Client $client,
    ) {
        parent::__construct($this->client);
    }

    public function serialize(IndexItemInterface $item): array
    {
        $contextBuilder = (new ObjectNormalizerContextBuilder())
            ->withGroups([IndexNames::Tags->value]);
        $contextBuilder = (new DateTimeNormalizerContextBuilder())
            ->withContext($contextBuilder)
            ->withFormat(IndexFieldTypes::DATEFORMAT);

        $data = $this->serializer->normalize($item, null, $contextBuilder->toArray());

        // @todo: Figure out how to do these changes with the serializer. This is just....
        // Get vocabulary names.
        // Use singular "vocabulary" because we want the api filter to use the singular form.
        $data['vocabulary'] = array_column($data['vocabularies'], 'name');
        unset($data['vocabularies']);

        return $data;
    }
}
