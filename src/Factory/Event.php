<?php

namespace App\Factory;

use App\Entity\Event as EventEntity;
use App\Entity\Feed;
use App\Exception\FactoryException;
use App\Model\Feed\FeedItem;
use App\Repository\EventRepository;
use App\Repository\FeedRepository;

class Event
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly FeedRepository $feedRepository,
        private readonly Location $locationFactory,
        private readonly Tags $tagsFactory,
        private readonly Occurrences $occurrencesFactory,
        private readonly Image $imageFactory,
    ) {
    }

    /**
     * @throws FactoryException
     */
    public function createOrUpdate(FeedItem $item): EventEntity
    {
        $feed = $this->feedRepository->findOneBy(['id' => $item->feedId]);
        if (is_null($feed)) {
            throw new FactoryException('Missing feed in event factory');
        }
        $entity = $this->get(['feed' => $feed, 'feedItemId' => $item->id]);
        $hash = $this->calculateHash($item);
        if (is_null($entity)) {
            $entity = new \App\Entity\Event();
            $entity->setHash($hash);
            $this->mapValues($entity, $item, $feed);

            // Make it stick.
            $this->eventRepository->save($entity, true);
        } else {
            // Check if hash has changed, before trying to update it.
            if ($entity->getHash() !== $hash) {
                $this->mapValues($entity, $item, $feed);
                $entity->setHash($hash);

                // Make it stick.
                $this->eventRepository->save($entity, true);
            }
        }

        return $entity;
    }

    public function get(array $criteria): ?EventEntity
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
     * Helper function to map feed items into event entities.
     *
     * @param eventEntity $entity
     *   Entity to map values to
     * @param feedItem $item
     *   The normalized feed item
     * @param feed $feed
     *   The feed that the item came from
     */
    private function mapValues(EventEntity $entity, FeedItem $item, Feed $feed): void
    {
        $entity->setDescription($item->description)
            ->setExcerpt($item->excerpt)
            ->setLanguageCode($item->landcode)
            ->setFeedItemId($item->id)
            ->setTicketUrl($item->ticketUrl)
            ->setUrl($item->url)
            ->setPublic($item->public)
            ->setOrganization($feed->getOrganization())
            ->setFeed($feed);

        if (!is_null($item->image)) {
            $this->imageFactory->createOrUpdate($item->image, $entity->getImage());
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
        foreach ($this->occurrencesFactory->createOrLookup($item->occurrences, $entity->getId()) as $occurrenceEntity) {
            $entity->addOccurrence($occurrenceEntity);
        }

        // @todo: Created_by (should we have feed user)
    }
}
