<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\NutritionistTeIndex;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NutritionistTeIndex>
 */
class NutritionistTeIndexRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NutritionistTeIndex::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('nti')
            ->where('nti.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('nti.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
