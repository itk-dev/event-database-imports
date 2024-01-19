<?php

namespace App\Factory;

use App\Entity\DailyOccurrence;
use App\Entity\Event;
use App\Entity\Occurrence;
use App\Model\DateTimeInterval;
use App\Repository\DailyOccurrenceRepository;
use App\Service\TimeInterface;
use Psr\Log\LoggerInterface;

final class DailyOccurrencesFactory
{
    public function __construct(
        private readonly TimeInterface $time,
        private readonly DailyOccurrenceRepository $dailyOccurrenceRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function createOrUpdate(Event $event): void
    {
        foreach ($event->getOccurrences() as $occurrence) {
            // Only look at existing daily occurrences in the context of the current occurrences.
            $exitingDailyOccurrences = $occurrence->getDailyOccurrences();

            // Each event occurrence can span more than one day.
            $start = $occurrence->getStart();
            $end = $occurrence->getEnd();
            if (isset($start, $end)) {
                $intervals = $this->time->getIntervals($start, $end);
                foreach ($exitingDailyOccurrences as $dailyOccurrence) {
                    foreach ($intervals as $id => $interval) {
                        // Check if the interval exists in the old daily occurrences based on timestamps.
                        if ($this->isEqualDates($interval, $dailyOccurrence)) {
                            $this->setValues($interval, $dailyOccurrence, $occurrence);
                            $this->dailyOccurrenceRepository->save($dailyOccurrence);

                            // Remove the processed interval from input.
                            unset($intervals[$id]);

                            // Jump to outer foreach.
                            continue 2;
                        }
                    }

                    // Daily occurrence not found in intervals, so remove it from event's daily occurrences.
                    $exitingDailyOccurrences->removeElement($dailyOccurrence);
                }

                // Create daily occurrences for remaining intervals.
                foreach ($intervals as $interval) {
                    $dailyOccurrence = new DailyOccurrence();
                    $this->setValues($interval, $dailyOccurrence, $occurrence);
                    $this->dailyOccurrenceRepository->save($dailyOccurrence);

                    $event->addDailyOccurrence($dailyOccurrence);
                }
            }
        }

        $this->dailyOccurrenceRepository->flush();
    }

    /**
     * Check if the dates are the same in the interval and the daily occurrence.
     *
     * @param DateTimeInterval $interval
     *   Time interval for an occurrence
     * @param DailyOccurrence $dailyOccurrence
     *   The daily occurrence to test the interval against
     *
     * @return bool
     *   True if dates in the objects are equal
     */
    private function isEqualDates(DateTimeInterval $interval, DailyOccurrence $dailyOccurrence): bool
    {
        $occurrenceStartDate = $dailyOccurrence->getStart();
        $occurrenceEndDate = $dailyOccurrence->getEnd();
        if (!isset($occurrenceStartDate, $occurrenceEndDate, $interval->start, $interval->end)) {
            // This should not happen.
            $this->logger->critical(sprintf('Daily occurrences (id: %d) entity has dates that are null', $dailyOccurrence->getId() ?? '-1'));

            return false;
        }

        return $occurrenceStartDate->getTimestamp() === $interval->start->getTimestamp()
          && $occurrenceEndDate->getTimestamp() === $interval->end->getTimestamp();
    }

    /**
     * Helper to set value form DateTimeInterval to daily occurrence entity.
     *
     * @param DateTimeInterval $interval
     *   Time interval for the occurrence
     * @param DailyOccurrence $dailyOccurrence
     *   The entity to set value for
     * @param Occurrence $occurrence
     *   The occurrence the daily occurrence is based on
     */
    private function setValues(DateTimeInterval $interval, DailyOccurrence $dailyOccurrence, Occurrence $occurrence): void
    {
        $dailyOccurrence->setStart($interval->start)
            ->setEnd($interval->end)
            ->setStatus($occurrence->getStatus())
            ->setRoom($occurrence->getRoom())
            ->setStatus($occurrence->getStatus())
            ->setEvent($occurrence->getEvent())
            ->setTicketPriceRange($occurrence->getTicketPriceRange())
            ->setOccurrence($occurrence);
    }
}
