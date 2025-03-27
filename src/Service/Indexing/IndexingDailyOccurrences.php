<?php

namespace App\Service\Indexing;

use App\Entity\DailyOccurrence;
use App\Model\Indexing\IndexFieldTypes;
use App\Model\Indexing\IndexNames;
use Elastic\Elasticsearch\Client;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Serializer\Context\Normalizer\DateTimeNormalizerContextBuilder;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsTaggedItem(index: IndexNames::DailyOccurrences->value, priority: 10)]
final class IndexingDailyOccurrences extends AbstractIndexingElastic
{
    protected const string INDEX_ALIAS = IndexNames::DailyOccurrences->value;

    public function __construct(
        private readonly IndexingEvents $indexingEvents,
        private readonly SerializerInterface $serializer,
        private readonly Client $client,
    ) {
        parent::__construct($this->client);
    }

    public function serialize(IndexItemInterface $item): array
    {
        if (!$item instanceof DailyOccurrence) {
            throw new \InvalidArgumentException('Item must be an instance of DailyOccurrence.');
        }

        $updatedAt = $this->getUpdatedAt($item);
        $item->setUpdatedAt($updatedAt);

        $contextBuilder = (new ObjectNormalizerContextBuilder())
            ->withGroups([IndexNames::Occurrences->value]);
        $contextBuilder = (new DateTimeNormalizerContextBuilder())
            ->withContext($contextBuilder)
            ->withTimezone('Europe/Copenhagen')
            ->withFormat(IndexFieldTypes::DATEFORMAT);

        $data = $this->serializer->normalize($item, null, $contextBuilder->toArray());

        // @todo: Figure out how to do these changes with the serializer. This is just....
        $data['event'] = $this->indexingEvents->serialize($item->getEvent());

        return $data;
    }

    private function getUpdatedAt(DailyOccurrence $occurrence): \DateTime
    {
        $updatedAt = $occurrence->getUpdatedAt();
        $event = $occurrence->getEvent();

        $updatedAt = max($updatedAt, $event->getOrganization()?->getUpdatedAt());
        $updatedAt = max($updatedAt, $event->getLocation()?->getUpdatedAt());
        $updatedAt = max($updatedAt, $event->getImage()?->getUpdatedAt());

        foreach ($event->getPartners() as $partner) {
            $updatedAt = max($updatedAt, $partner->getUpdatedAt());
        }

        foreach ($event->getOccurrences() as $occurrence) {
            $updatedAt = max($updatedAt, $occurrence->getUpdatedAt());
        }

        return $updatedAt;
    }
}
