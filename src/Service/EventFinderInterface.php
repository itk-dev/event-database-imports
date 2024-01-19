<?php

namespace App\Service;

use App\Exception\NotSupportedEntityException;

interface EventFinderInterface
{
    /**
     * Find all events linked to the entity given.
     *
     * @param object $entity
     *   The entity to find events for
     *
     * @return iterable
     *   All events linked to that entity
     *
     *  @throws NotSupportedEntityException
     *   If the entity given is not supported by the implementation
     */
    public function findEvents(object $entity): iterable;
}
