<?php

namespace App\Service\Indexing;

use App\Model\Indexing\IndexFieldTypes;
use App\Model\Indexing\IndexNames;
use App\Service\ImageHandlerInterface;
use Elastic\Elasticsearch\Client;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Serializer\Context\Normalizer\DateTimeNormalizerContextBuilder;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsTaggedItem(index: IndexNames::Events->value, priority: 10)]
final class IndexingEvents extends AbstractIndexingElastic
{
    protected const string INDEX_ALIAS = IndexNames::Events->value;

    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ImageHandlerInterface $imageHandler,
        private readonly Client $client,
    ) {
        parent::__construct($this->client);
    }

    public function serialize(IndexItemInterface $item): array
    {
        $contextBuilder = (new ObjectNormalizerContextBuilder())
            ->withGroups([IndexNames::Events->value]);
        $contextBuilder = (new DateTimeNormalizerContextBuilder())
            ->withContext($contextBuilder)
            ->withFormat(IndexFieldTypes::DATEFORMAT);
        $data = $this->serializer->normalize($item, null, $contextBuilder->toArray());

        // @todo: Figure out how to do these changes with the serializer. This is just....
        // Get tag names.
        $data['tags'] = array_column($data['tags'], 'name');

        // Flatten location address and convert lang/long to coordinate point.
        $data['location'] += $data['location']['address'];
        unset($data['location']['address']);
        $data['location']['coordinates'] = [$data['location']['latitude'], $data['location']['longitude']];
        unset($data['location']['latitude'], $data['location']['longitude']);

        // Fix image urls (with a full path and derived sizes).
        $imageUrl = $data['imageUrls']['original'];
        $data['imageUrls'] = $this->imageHandler->getTransformedImageUrls($imageUrl);

        return $data;
    }
}
