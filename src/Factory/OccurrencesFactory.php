<?php

namespace App\Factory;

use App\Entity\Event;
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
     * @param array<FeedItemOccurrence> $input
     *   The tag names to create/lockup in the database as strings
     * @param Event $event
     *   The event related to the occurrences
     */
    public function createOrLookup(array $input, Event $event): void
    {
        $eventOccurrences = $event->getOccurrences();
        foreach ($eventOccurrences as $occurrence) {
            foreach ($input as $id => $item) {
                // Check if item exist in the old occurrences base on timestamps.
                if (0 === $occurrence->getStart()->diff($item->start)->s && 0 === $occurrence->getEnd()->diff($item->end)->s) {
                    // Update with new values.
                    $this->setValues($item, $occurrence);
                    $this->occurrenceRepository->save($occurrence);

                    // Removed processed occurrence from input.
                    unset($input[$id]);

                    // Jump to outer foreach.
                    continue 2;
                }
            }

            // Not found in input, so remove it.
            $event->getOccurrences()->removeElement($occurrence);
        }

        // Loop over remaining input elements.
        foreach ($input as $item) {
            $occurrence = new Occurrence();
            $this->setValues($item, $occurrence);
            $this->occurrenceRepository->save($occurrence);

            // Add the new occurrence to the event.
            $event->addOccurrence($occurrence);
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
