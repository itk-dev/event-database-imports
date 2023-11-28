<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Image;
use App\Entity\Location;
use App\Entity\Tag;
use App\Repository\TagRepository;

final class Finder
{
    public function __construct(
        private readonly TagRepository $tagRepository,
    ) {
    }

    public function search(string $className, object $entity): iterable
    {
        switch ($className) {
            case Image::class:
                // Update/download image and find all events using that image and reindex the events.

                break;

            case Tag::class:
                // Find all events with that tag and reindex
                return $this->findEventsFromTagId($entity->getId());
                break;

            case Address::class:
                // Find all locations using this address and update all events using that location.

                break;

            case Location::class:
                // Find all events using this location.

                break;
        }
    }

    private function findEventsFromTagId(int $tagId): iterable
    {
        $tag = $this->tagRepository->find($tagId);

        return $tag->getEvents();
    }
}
