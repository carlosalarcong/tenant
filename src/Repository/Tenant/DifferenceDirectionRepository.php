<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\DifferenceDirection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DifferenceDirection>
 */
class DifferenceDirectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DifferenceDirection::class);
    }

    /**
     * Encuentra todos los sentidos de diferencia activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('dd')
            ->where('dd.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('dd.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
