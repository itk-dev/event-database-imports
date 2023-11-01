<?php

namespace App\Repository;

use App\Entity\DailyOccurrence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DailyOccurrence>
 *
 * @method DailyOccurrence|null find($id, $lockMode = null, $lockVersion = null)
 * @method DailyOccurrence|null findOneBy(array $criteria, array $orderBy = null)
 * @method DailyOccurrence[]    findAll()
 * @method DailyOccurrence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class DailyOccurrenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyOccurrence::class);
    }

    public function save(DailyOccurrence $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DailyOccurrence $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
