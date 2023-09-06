<?php

namespace App\Factory;

use App\Model\Feed\FeedItem;
use App\Repository\EventRepository;
use App\Repository\FeedRepository;
use App\Repository\OrganizationRepository;
use App\Entity\Event as EventEntity;

class Event
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly FeedRepository $feedRepository,
        private readonly OrganizationRepository $organizationRepository,
    )
    {
    }

    public function create(FeedItem $item): EventEntity
    {
        $feed = $this->feedRepository->findOneBy(['id' => $item->feedId]);
        $entity = $this->get(['feed' => $feed, 'feedItemId' => $item->id]);
        $hash = $this->calculateHash($item);
        if (is_null($entity)) {
            $entity = new \App\Entity\Event();
            $entity->setDescription($item->description)
                ->setExcerpt($item->excerpt)
                ->setFeedItemId($item->id)
                ->setFeed($feed)
                ->setHash($hash)
                ->setTicketUrl($item->ticketUrl)
                ->setUrl($item->url)
                ->setPublic($item->public);
            $this->eventRepository->save($entity, true);

            // Public config

            // Org

            // Image

            // langcode

            // Location -> address

            // Created_by (should we have feed user)
        } else {
            // Check if hash has changed.
            if ($entity->getHash() !== $hash) {
                // Update.
                $t=1;
            }
        }

        return $entity;
    }

    public function get(array $criteria): ?EventEntity
    {
        return $this->eventRepository->findOneBy($criteria);
    }

    private function calculateHash(FeedItem $item): string
    {
        return hash('sha256', serialize($item));
    }
}
