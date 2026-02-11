<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\CancellationReason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CancellationReason>
 */
class CancellationReasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CancellationReason::class);
    }

    /**
     * Encuentra todos los motivos activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('cr')
            ->where('cr.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('cr.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
