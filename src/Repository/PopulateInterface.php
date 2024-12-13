<?php

namespace App\Repository;

use Doctrine\Common\Collections\Criteria;

interface PopulateInterface
{
    /**
     * Counts entities by a set of criteria.
     *
     * @psalm-param array<string, mixed> $criteria
     *
     * @return int the cardinality of the objects that match the given criteria
     */
    public function count(array $criteria);

    public function findToPopulate(array $criteria, int $limit, int $offset): array;

    public function countToPopulate(array $criteria): int;
}
