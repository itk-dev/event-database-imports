<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Image;
use App\Entity\Location;
use App\Entity\Tag;
use App\Exception\NotSupportedEntityException;

final class EventFinder implements EventFinderInterface
{
    public function findEvents(object $entity): iterable
    {
        switch (get_class($entity)) {
            case Image::class:
                yield $entity->getEvent();
                break;

            case Tag::class:
                yield from $this->findEventsFromTag($entity);
                break;

            case Address::class:
                yield from $this->findEventsFromAddress($entity);
                break;

            case Location::class:
                yield from $this->findEventsFromLocation($entity);
                break;

            default:
                throw new NotSupportedEntityException(sprintf('The class "%s" is not supported by the EventFinder service', get_class($entity)));
        }
    }

    /**
     * Find all events with using the provided tag.
     *
     * @param Tag $tag
     *   Tag to find events linked too
     *
     * @return iterable
     *   All events that use the tag
     */
    private function findEventsFromTag(Tag $tag): iterable
    {
        foreach ($tag->getEvents() as $event) {
            yield $event;
        }
    }

    /**
     * Find all events based on the address given.
     *
     * @param Address $address
     *   Address to find events from
     *
     * @return iterable
     *   All events using the address
     */
    private function findEventsFromAddress(Address $address): iterable
    {
        foreach ($address->getLocations() as $location) {
            yield from $this->findEventsFromLocation($location);
        }
    }

    /**
     * Find all events with the give location.
     *
     * @param Location $location
     *   Location to find event for
     *
     * @return iterable
     *   All events using the location
     */
    private function findEventsFromLocation(Location $location): iterable
    {
        foreach ($location->getEvents() as $event) {
            yield $event;
        }
    }
}
