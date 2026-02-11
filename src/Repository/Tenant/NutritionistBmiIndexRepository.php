<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\NutritionistBmiIndex;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NutritionistBmiIndex>
 */
class NutritionistBmiIndexRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NutritionistBmiIndex::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('nbi')
            ->where('nbi.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('nbi.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
