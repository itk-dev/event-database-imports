<?php

namespace App\Service\Indexing;

use App\Model\Indexing\IndexFieldTypes;
use App\Model\Indexing\IndexNames;
use Elastic\Elasticsearch\Client;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Serializer\Context\Normalizer\DateTimeNormalizerContextBuilder;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsTaggedItem(index: IndexNames::Locations->value, priority: 10)]
final class IndexingLocations extends AbstractIndexingElastic
{
    protected const string INDEX_ALIAS = IndexNames::Locations->value;

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly Client $client,
    ) {
        parent::__construct($this->client);
    }

    public function serialize(IndexItemInterface $item): array
    {
        $contextBuilder = (new ObjectNormalizerContextBuilder())
            ->withGroups([IndexNames::Locations->value]);
        $contextBuilder = (new DateTimeNormalizerContextBuilder())
            ->withContext($contextBuilder)
            ->withTimezone('Europe/Copenhagen')
            ->withFormat(IndexFieldTypes::DATEFORMAT);
        $data = $this->serializer->normalize($item, null, $contextBuilder->toArray());

        // Flatten location address and convert lang/long to coordinate point.
        $data += $data['address'];
        unset($data['address']);
        $data['coordinates'] = [$data['latitude'], $data['longitude']];
        unset($data['latitude'], $data['longitude']);

        return $data;
    }
}
