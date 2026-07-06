<?php

namespace App\Repository;

use App\Model\Indexing\IndexNames;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * @template T of object
 *
 * @extends ServiceEntityRepository<T>
 */
#[AsTaggedItem(index: IndexNames::Events->value, priority: 10)]
abstract class AbstractPopulateRepository extends ServiceEntityRepository implements PopulateInterface
{
    #[\Override]
    public function findToPopulate(array $criteria, int $limit, int $offset): array
    {
        return $this->findBy($criteria, ['id' => Criteria::ASC], $limit, $offset);
    }

    #[\Override]
    public function countToPopulate(array $criteria): int
    {
        return $this->count($criteria);
    }
}
