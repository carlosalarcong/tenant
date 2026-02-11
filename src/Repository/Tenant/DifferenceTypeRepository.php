<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\DifferenceType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DifferenceType>
 */
class DifferenceTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DifferenceType::class);
    }

    /**
     * Encuentra todos los tipos de diferencia activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('dt')
            ->where('dt.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('dt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
