<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\GratuityType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GratuityType>
 */
class GratuityTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GratuityType::class);
    }

    /**
     * Encuentra todos los tipos de gratuidad activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('gt')
            ->where('gt.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('gt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
