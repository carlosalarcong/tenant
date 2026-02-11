<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\DifferenceReason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DifferenceReason>
 */
class DifferenceReasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DifferenceReason::class);
    }

    /**
     * Encuentra todos los motivos de diferencia activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('dr')
            ->where('dr.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('dr.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
