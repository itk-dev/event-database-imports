<?php
/**
 * @file
 * Contains a service to populate search index.
 */

namespace App\Service;

use App\Exception\IndexingException;
use App\Model\Indexing\Criteria\PopulateCriteriaFactory;
use App\Repository\PopulateInterface;
use App\Service\Indexing\IndexingInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

/**
 * Class Populate.
 */
final class Populate
{
    final public const int BATCH_SIZE = 100;
    final public const int LOCK_TIMEOUT = 3600;
    final public const int DEFAULT_RECORD_ID = -1;

    private LockInterface $lock;

    public function __construct(
        private readonly iterable $indexingServices,
        private readonly iterable $repositories,
        private readonly PopulateCriteriaFactory $criteriaFactory,
        private readonly LockFactory $lockFactory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Populate the search index with Search entities.
     *
     * @param string $index
     *   Name of the index to populate
     * @param int $record_id
     *   Limit populate to this single search record id
     * @param bool $force
     *   Force execution ignoring locks (default false)
     *
     * @return \Generator
     *   Will yield back progress and error messages
     *
     * @throws IndexingException
     */
    public function populate(string $index, int $record_id = self::DEFAULT_RECORD_ID, bool $force = false): \Generator
    {
        /** @var IndexingInterface[] $indexingServices */
        $indexingServices = $this->indexingServices instanceof \Traversable ? iterator_to_array($this->indexingServices) : $this->indexingServices;
        /** @var PopulateInterface[] $repositories */
        $repositories = $this->repositories instanceof \Traversable ? iterator_to_array($this->repositories) : $this->repositories;

        if ($this->acquireLock($force)) {
            $numberOfRecords = 1;
            if (-1 === $record_id) {
                $numberOfRecords = $repositories[$index]->count([]);
            }

            // Make sure there are entries in the Search table to process.
            if (0 === $numberOfRecords) {
                yield sprintf('%s: No entries in Search table.', ucfirst($index));

                return;
            }

            $entriesAdded = 0;

            while ($entriesAdded < $numberOfRecords) {
                $criteria = [];
                if ($this::DEFAULT_RECORD_ID !== $record_id) {
                    $criteria = ['id' => $record_id];
                }

                $criteria = $this->criteriaFactory->getPopulateCriteria($index);

                // $entities = $repositories[$index]->findBy($criteria, ['id' => 'ASC'], self::BATCH_SIZE, $entriesAdded);
                $entities = $repositories[$index]->findToPopulate($criteria, self::BATCH_SIZE, $entriesAdded);

                // No more results.
                if (0 === count($entities)) {
                    yield sprintf('%s: %s of %s processed. No more results.', ucfirst($index), number_format($entriesAdded, 0, ',', '.'), number_format($numberOfRecords, 0, ',', '.'));
                    break;
                }

                if ($this::DEFAULT_RECORD_ID === $record_id) {
                    // Send bulk.
                    $indexingServices[$index]->bulk($entities);
                } else {
                    // Single indexing is beneficial for debug as Elastic searches bulk indexing don't throw errors but
                    // always returns HTTP_OK as the items are sent into an intern queue in Elastic.
                    $indexingServices[$index]->index(reset($entities));
                }

                $entriesAdded += count($entities);

                // Update progress message.
                yield sprintf('%s: %s of %s added', ucfirst($index), number_format($entriesAdded, 0, ',', '.'), number_format($numberOfRecords, 0, ',', '.'));

                // Free up memory usages.
                $this->entityManager->clear();
                gc_collect_cycles();
            }

            if ($this::DEFAULT_RECORD_ID === $record_id) {
                // If single item was indexed there is no new index created, so don't try to switch indexes.
                yield sprintf('<info>%s: Switching alias and removing old index</info>', ucfirst($index));
                $indexingServices[$index]->switchIndex();
            }

            $this->releaseLock();
        } else {
            yield sprintf('<error>%s: Process is already running use "--force" to run command</error>', ucfirst($index));
        }
    }

    /**
     * Get process lock.
     *
     * Used to prevent more than one populating process running at once.
     *
     * @param bool $force
     *  Force execution ignoring locks (default false)
     *
     *   If lock acquired true else false
     */
    private function acquireLock(bool $force = false): bool
    {
        $this->lock = $this->lockFactory->createLock('app:populate:lock', $this::LOCK_TIMEOUT, false);

        if ($this->lock->acquire() || $force) {
            return true;
        }

        return false;
    }

    /**
     * Release the populating process lock.
     */
    private function releaseLock(): void
    {
        $this->lock->release();
    }
}
