<?php

namespace App\Factory;

use App\Entity\Occurrence;
use App\Model\Feed\FeedItemOccurrence;
use App\Repository\OccurrenceRepository;

final class OccurrencesFactory
{
    public function __construct(
        private readonly OccurrenceRepository $occurrenceRepository
    ) {
    }

    /**
     * Create occurrences or find matching in the database.
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
            $occurrence = $this->occurrenceRepository->findOneBy(['start' => $item->start, 'end' => $item->end]);
            if (!is_null($occurrence)) {
                if ($occurrence->getEvent()?->getId() == $eventId) {
                    $this->setValues($item, $occurrence);
                    $this->occurrenceRepository->save($occurrence);

                    yield $occurrence;
                    continue;
                }
            }

            $occurrence = new Occurrence();
            $this->setValues($item, $occurrence);
            $this->occurrenceRepository->save($occurrence);

            yield $occurrence;
        }
    }

    /**
     * Helper to set value form feed item occurrences to database occurrence.
     *
     * @param FeedItemOccurrence $feedItemOccurrence
     *   Normalized feed occurrence
     * @param Occurrence $occurrence
     *   Database occurrences entity
     */
    private function setValues(FeedItemOccurrence $feedItemOccurrence, Occurrence $occurrence): void
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
