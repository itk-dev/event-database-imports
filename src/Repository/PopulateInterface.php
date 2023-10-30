<?php

namespace App\Repository;

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
