<?php

namespace App\Factory;

use App\Entity\Event;
use App\Entity\Feed;
use App\Exception\FactoryException;
use App\Model\Feed\FeedItem;
use App\Repository\EventRepository;
use App\Repository\FeedRepository;

final class EventFactory
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly FeedRepository $feedRepository,
        private readonly LocationFactory $locationFactory,
        private readonly TagsFactory $tagsFactory,
        private readonly OccurrencesFactory $occurrencesFactory,
        private readonly ImageFactory $imageFactory,
    ) {
    }

    /**
     * @throws FactoryException
     */
    public function createOrUpdate(FeedItem $item): Event
    {
        $feed = $this->feedRepository->findOneBy(['id' => $item->feedId]);
        if (is_null($feed)) {
            throw new FactoryException('Missing feed in event factory');
        }
        $entity = $this->get(['feed' => $feed, 'feedItemId' => $item->id]);
        $hash = $this->calculateHash($item);
        if (is_null($entity)) {
            $entity = new Event();
            $entity->setHash($hash);
            $this->setValues($entity, $item, $feed);

            // Make it stick.
            $this->eventRepository->save($entity, true);
        } else {
            // Check if hash has changed, before trying to update it.
            if ($entity->getHash() !== $hash) {
                $this->setValues($entity, $item, $feed);
                $entity->setHash($hash);

                // Make it stick.
                $this->eventRepository->save($entity, true);
            }
        }

        return $entity;
    }

    /**
     * Determine it the FeedItem is updatable or is a new FeedItem.
     *
     * @param FeedItem $item
     *   The feed item to test
     *
     * @return bool
     *   True if updatable or new feed item
     *
     * @throws FactoryException
     */
    public function isUpdatableOrNew(FeedItem $item): bool
    {
        $feed = $this->feedRepository->findOneBy(['id' => $item->feedId]);
        if (is_null($feed)) {
            throw new FactoryException('Missing feed in event factory');
        }

        $entity = $this->get(['feed' => $feed, 'feedItemId' => $item->id]);
        if (!is_null($entity) && $entity->getHash() === $this->calculateHash($item)) {
            // Entity exists for the item and the hash has not changed.
            return false;
        }

        return true;
    }

    /**
     * Look up event in database based on field criteria.
     *
     * @param array $criteria
     *   Doctrine field criteria
     *
     * @return Event|null
     *   Event entity or null if not found
     */
    public function get(array $criteria): ?Event
    {
        return $this->eventRepository->findOneBy($criteria);
    }

    /**
     * Calculate hash string base on typed value event object.
     *
     * This hash should be used to see if event have changed it's content.
     *
     * @param feedItem $item
     *   The feed item object to calculate hash value for
     *
     * @return string
     *   The calculated hash string
     */
    private function calculateHash(FeedItem $item): string
    {
        return hash('sha256', serialize($item));
    }

    /**
     * Helper function to set feed items into event entities.
     *
     * @param Event $entity
     *   Entity to map values to
     * @param feedItem $item
     *   The normalized feed item
     * @param feed $feed
     *   The feed that the item came from
     */
    private function setValues(Event $entity, FeedItem $item, Feed $feed): void
    {
        $entity->setTitle($item->title)
            ->setDescription($item->description)
            ->setExcerpt($item->excerpt)
            ->setFeedItemId($item->id)
            ->setTicketUrl($item->ticketUrl)
            ->setUrl($item->url)
            ->setPublic($item->public)
            ->setOrganization($feed->getOrganization())
            ->setFeed($feed);

        if (!is_null($item->image)) {
            $image = $this->imageFactory->createOrUpdate($item->image, $entity->getImage());
            $entity->setImage($image);
        }

        if (!is_null($item->tags)) {
            foreach ($this->tagsFactory->createOrLookup($item->tags) as $tag) {
                $entity->addTag($tag);
            }
        }

        if (!is_null($item->location)) {
            $entity->setLocation($this->locationFactory->createOrUpdate($item->location));
        }

        // The feed items may come with occurrences The daly occurrences will be handled later on.
        $this->occurrencesFactory->createOrLookup($item->occurrences, $entity);

        // @todo: Created_by (should we have feed user)
    }
}
