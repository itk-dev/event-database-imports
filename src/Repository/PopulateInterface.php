<?php

namespace App\Repository;

use App\Exception\IndexingException;
use App\Service\Indexing\IndexItemInterface;

interface PopulateInterface
{
    /**
     * Find the number of records in the database.
     *
     * @return int
     *   The number of records
     */
    public function getNumberOfRecords(): int;
}
