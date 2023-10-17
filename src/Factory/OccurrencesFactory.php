<?php

namespace App\Factory;

use App\Entity\Event;
use App\Entity\Occurrence;
use App\Model\Feed\FeedItemOccurrence;
use App\Repository\OccurrenceRepository;
use Psr\Log\LoggerInterface;

final class OccurrencesFactory
{
    public function __construct(
        private readonly OccurrenceRepository $occurrenceRepository,
        private readonly LoggerInterface $logger
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
                if ($this->isEqualDates($item, $occurrence, $event)) {
                    // Update with new values.
                    $this->setValues($item, $occurrence);
                    $this->occurrenceRepository->save($occurrence);

                    // Removed processed occurrence from input.
                    unset($input[$id]);

                    // Jump to outer foreach.
                    continue 2;
                }
            }

            // Occurrence not found in input, so remove it from event.
            $eventOccurrences->removeElement($occurrence);
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
     * Compare dates for an occurrence.
     *
     * @param FeedItemOccurrence $item
     *   The new input occurrence
     * @param occurrence $occurrence
     *   An occurrences from an exiting database backed event
     * @param Event $event
     *   The event in question
     *
     * @return bool
     *   True if dates are equal else false
     */
    private function isEqualDates(FeedItemOccurrence $item, Occurrence $occurrence, Event $event): bool
    {
        $occurrenceStartDate = $occurrence->getStart();
        $occurrenceEndDate = $occurrence->getEnd();
        if (!isset($occurrenceStartDate, $occurrenceEndDate, $item->start, $item->end)) {
            // This should not happen.
            $this->logger->critical(sprintf('Event (id: %d) has occurrences dates that are null', $event->getId() ?? '-1'));

            // We return null to remove the event.
            return false;
        }

        return $occurrenceStartDate->getTimestamp() === $item->start->getTimestamp()
          && $occurrenceEndDate->getTimestamp() === $item->end->getTimestamp();
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
        $occurrence->setTicketPriceRange($feedItemOccurrence->price);
        $occurrence->setRoom($feedItemOccurrence->room);
        $occurrence->setStatus($feedItemOccurrence->status);
    }
}
