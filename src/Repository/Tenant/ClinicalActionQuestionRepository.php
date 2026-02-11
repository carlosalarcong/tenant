<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ClinicalActionQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClinicalActionQuestion>
 */
class ClinicalActionQuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClinicalActionQuestion::class);
    }

    /**
     * @return ClinicalActionQuestion[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('c.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ClinicalActionQuestion[]
     */
    public function findByCategory(int $categoryId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.category = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('c.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
