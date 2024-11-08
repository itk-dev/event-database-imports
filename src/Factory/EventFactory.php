<?php

namespace App\Factory;

use App\Entity\Event;
use App\Entity\Feed;
use App\Entity\FeedItem;
use App\Exception\FactoryException;
use App\Model\Feed\FeedItemData;
use App\Repository\EventRepository;
use App\Repository\FeedRepository;
use App\Utils\UriHelper;
use Psr\Log\LoggerInterface;

final readonly class EventFactory
{
    public function __construct(
        private EventRepository $eventRepository,
        private FeedRepository $feedRepository,
        private LocationFactory $locationFactory,
        private OrganizationFactory $organizationFactory,
        private TagsFactory $tagsFactory,
        private OccurrencesFactory $occurrencesFactory,
        private ImageFactory $imageFactory,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws FactoryException
     */
    public function createOrUpdate(FeedItemData $item, FeedItem $feedItemEntity): Event
    {
        $feed = $this->feedRepository->findOneBy(['id' => $item->feedId]);
        if (is_null($feed)) {
            throw new FactoryException('Missing feed in event factory');
        }
        $editedBy = $feed->getUser() ?? $feed;

        $event = $feedItemEntity->getEvent();

        if (is_null($event)) {
            $event = new Event();

            $event->setCreatedBy((string) $editedBy);
            $event->setUpdatedBy((string) $editedBy);
        } else {
            $event->setUpdatedBy((string) $editedBy);
        }

        $this->setValues($event, $feedItemEntity, $item, $feed);
        $this->eventRepository->save($event, true);

        return $event;
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
    public function getEvent(array $criteria): ?Event
    {
        return $this->eventRepository->findOneBy($criteria);
    }

    /**
     * Helper function to set feed items into event entities.
     *
     * @param Event $entity
     *   Entity to map values to
     * @param FeedItemData $item
     *   The normalized feed item
     * @param Feed $feed
     *   The feed that the item came from
     */
    private function setValues(Event $entity, FeedItem $feedItemEntity, FeedItemData $item, Feed $feed): void
    {
        $base = $feed->getConfiguration()['base'] ?? null;

        $entity->setTitle($item->title ?? '')
            ->setDescription($item->description)
            ->setExcerpt($item->excerpt)
            ->setFeedItem($feedItemEntity)
            ->setPublicAccess($item->publicAccess)
            ->setFeed($feed)
            ->setFeedItem($feedItemEntity);

        $description = $entity->getDescription();

        if (null !== $item->ticketUrl && '' !== $item->ticketUrl) {
            try {
                $entity->setTicketUrl(UriHelper::getAbsoluteUrl($item->ticketUrl, $base));
            } catch (\RuntimeException $exception) {
                $this->logger->error('Ticket URL error: '.$exception->getMessage());
            }
        } else {
            $entity->setTicketUrl(null);
        }

        if (null !== $item->url && '' !== $item->url) {
            try {
                $entity->setUrl(UriHelper::getAbsoluteUrl($item->url, $base));
            } catch (\RuntimeException $exception) {
                $this->logger->error('Event URL error: '.$exception->getMessage());
            }
        }

        if (!is_null($item->image)) {
            $image = $this->imageFactory->createOrUpdate($item->image, $entity->getImage(), $base);
            $entity->setImage($image);
        }

        if (!is_null($item->tags)) {
            foreach ($this->tagsFactory->createOrLookup($item->tags) as $tag) {
                $entity->addTag($tag);
            }
        }

        if (!is_null($item->location)) {
            $entity->setLocation($this->locationFactory->createOrUpdate($item->location, $base));
        }

        if (!is_null($item->organization)) {
            $entity->setOrganization($this->organizationFactory->createOrUpdate($item->organization));
        } else {
            $entity->setOrganization($feed->getOrganization());
        }

        foreach ($item->partners as $partner) {
            $partner = $this->organizationFactory->createOrUpdate($partner);
            $partner->addPartnerEvent($entity);
        }

        // The feed items may come with occurrences The daly occurrences will be handled later on.
        $this->occurrencesFactory->createOrLookup($item->occurrences, $entity);
    }
}
