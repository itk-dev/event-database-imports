<?php

namespace App\Repository;

use App\Entity\Vocabulary;
use App\Model\Indexing\IndexNames;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * @extends AbstractPopulateRepository<Vocabulary>
 *
 * @method Vocabulary|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vocabulary|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vocabulary[]    findAll()
 * @method Vocabulary[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
#[AsTaggedItem(index: IndexNames::Vocabularies->value, priority: 10)]
final class VocabularyRepository extends AbstractPopulateRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vocabulary::class);
    }

    public function save(Vocabulary $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Vocabulary $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
