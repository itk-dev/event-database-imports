<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Feed;
use App\Model\Feed\FeedItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function save(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function updateOrCreate(string $hash, Feed $feed, FeedItem $item): Event
    {
        $entity = $this->findOneBy(['feed' => $feed, 'feedItemId' => $item->id]);
        if (is_null($entity)) {
            $entity = new Event();
            $entity->setDescription($item->description)
                ->setExcerpt($item->excerpt)
                ->setFeedItemId($item->id)
                ->setFeed($feed)
                ->setHash($hash)
                ->setTicketUrl($item->ticketUrl)
                ->setUrl($item->url);
            $this->getEntityManager()->persist($entity);
            $this->getEntityManager()->flush();
            // Public config
            // Org
            // Image
            // langcode
            // Created_by (should we have feed user)
        } else {
            // Check if hash has changed.
            if ($entity->getHash() !== $hash) {
                // Update.
            }
        }

        return $entity;
    }
}
