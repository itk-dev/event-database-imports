<?php

namespace App\Repository;

use App\Entity\Feed;
use App\Entity\FeedItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FeedItem>
 */
class FeedItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeedItem::class);
    }

    public function save(FeedItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FeedItem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByByLastSeen(Feed $feed, \DateTimeInterface $after): iterable
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('feedItem')
            ->from(FeedItem::class, 'feedItem')
            ->where('feedItem.lastSeenAt < :seen')
            ->andWhere('feedItem.feed = :feed')
            ->setParameter('seen', $after)
            ->setParameter('feed', $feed);

        return $qb->getQuery()->toIterable();
    }
}
