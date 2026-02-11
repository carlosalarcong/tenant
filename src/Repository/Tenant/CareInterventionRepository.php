<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\CareIntervention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CareIntervention>
 */
class CareInterventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CareIntervention::class);
    }

    /**
     * @return CareIntervention[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('c.description', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return CareIntervention[]
     */
    public function findByCategory(int $categoryId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.category = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->getQuery()
            ->getResult();
    }
}
