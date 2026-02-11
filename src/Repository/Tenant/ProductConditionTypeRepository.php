<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ProductConditionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductConditionType>
 */
class ProductConditionTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductConditionType::class);
    }

    /**
     * Encuentra todos los tipos de condición de producto activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('pct')
            ->where('pct.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pct.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
