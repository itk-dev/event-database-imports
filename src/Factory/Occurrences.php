<?php

namespace App\Factory;

use App\Entity\Occurrence;
use App\Entity\Tag;
use App\Model\Feed\FeedItemOccurrence;
use App\Repository\OccurrenceRepository;

class Occurrences
{
    public function __construct(
        private readonly OccurrenceRepository $occurrenceRepository
    ) {
    }

    /**
     * Create tag or find matching in the database.
     *
     * @param array<FeedItemOccurrence> $occurrences
     *   The tag names to create/lockup in the database as strings
     * @param ?int $eventId
     *   The id of the event related to the occurrences
     *
     * @return iterable<Occurrence>
     *   Yield tag entities from the database
     */
    public function createOrLookup(array $occurrences, ?int $eventId): iterable
    {
        foreach ($occurrences as $item) {
            $occurrencesEntity = $this->occurrenceRepository->findOneBy(['start' => $item->start, 'end' => $item->end]);
            if (!is_null($occurrencesEntity)) {
                if ($occurrencesEntity->getEvent()?->getId() == $eventId) {
                    $this->mapValues($item, $occurrencesEntity);
                    $this->occurrenceRepository->save($occurrencesEntity);

                    yield $occurrencesEntity;
                    continue;
                }
            }

            $occurrencesEntity = new Occurrence();
            $this->mapValues($item, $occurrencesEntity);
            $this->occurrenceRepository->save($occurrencesEntity);

            yield $occurrencesEntity;
        }
    }

    /**
     * Helper to map value form feed item occurrences to database occurrence.
     *
     * @param FeedItemOccurrence $feedItemOccurrence
     *   Normalized feed occurrence
     * @param Occurrence $occurrence
     *   Database occurrences entity
     */
    private function mapValues(FeedItemOccurrence $feedItemOccurrence, Occurrence $occurrence): void
    {
        if (!is_null($feedItemOccurrence->start)) {
            $occurrence->setStart($feedItemOccurrence->start);
        }
        if (!is_null($feedItemOccurrence->end)) {
            $occurrence->setEnd($feedItemOccurrence->end);
        }
        if (!is_null($feedItemOccurrence->price)) {
            $occurrence->setTicketPriceRange($feedItemOccurrence->price);
        }
        if (!is_null($feedItemOccurrence->room)) {
            $occurrence->setRoom($feedItemOccurrence->room);
        }
        if (!is_null($feedItemOccurrence->status)) {
            $occurrence->setStatus($feedItemOccurrence->status);
        }
    }
}
