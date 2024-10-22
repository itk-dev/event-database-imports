<?php

namespace App\Service\Indexing;

use App\Entity\Event;
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
        private readonly IndexingLocations $indexingLocations,
        private readonly SerializerInterface $serializer,
        private readonly ImageHandlerInterface $imageHandler,
        private readonly Client $client,
    ) {
        parent::__construct($this->client);
    }

    public function serialize(IndexItemInterface $item): array
    {
        if (!$item instanceof Event) {
            throw new \InvalidArgumentException('Item must be an instance of Event.');
        }

        $contextBuilder = (new ObjectNormalizerContextBuilder())
            ->withGroups([IndexNames::Events->value]);
        $contextBuilder = (new DateTimeNormalizerContextBuilder())
            ->withContext($contextBuilder)
            ->withTimezone('Europe/Copenhagen')
            ->withFormat(IndexFieldTypes::DATEFORMAT);
        $data = $this->serializer->normalize($item, null, $contextBuilder->toArray());

        // @todo: Figure out how to do these changes with the serializer. This is just....
        // Get tag names.
        $data['tags'] = array_column($data['tags'], 'name');

        // @todo figure out why doctrine sometimes returns a keyed array. E.g. [1 => Occurrence]. This results in an "Indexing exception"
        $data['occurrences'] = array_values($data['occurrences']);
        $data['dailyOccurrences'] = array_values($data['dailyOccurrences']);

        // Fix image urls (with a full path and derived sizes).
        if ($data['imageUrls']) {
            $imageUrl = $data['imageUrls']['original'];
            $data['imageUrls'] = is_null($imageUrl) ? [] : $this->imageHandler->getTransformedImageUrls($imageUrl);
        }

        // @todo: Figure out how to do these changes with the serializer. This is just....
        // @todo: Handle validation. Location should never be null
        $location = $item->getLocation();
        $data['location'] = null === $location ? null : $this->indexingLocations->serialize($location);

        return $data;
    }
}
