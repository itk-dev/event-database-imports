<?php

namespace App\Repository;

use App\Entity\DailyOccurrence;
use App\Model\Indexing\IndexNames;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * @extends ServiceEntityRepository<DailyOccurrence>
 *
 * @method DailyOccurrence|null find($id, $lockMode = null, $lockVersion = null)
 * @method DailyOccurrence|null findOneBy(array $criteria, array $orderBy = null)
 * @method DailyOccurrence[]    findAll()
 * @method DailyOccurrence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
#[AsTaggedItem(index: IndexNames::DailyOccurrences->value, priority: 10)]
final class DailyOccurrenceRepository extends ServiceEntityRepository implements PopulateInterface
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

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getNumberOfRecords(): int
    {
        $query = $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->getQuery();

        return $query->getSingleScalarResult();
    }
}
