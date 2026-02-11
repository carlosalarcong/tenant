<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\PrescriptionDispensation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrescriptionDispensation>
 */
class PrescriptionDispensationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrescriptionDispensation::class);
    }

    /**
     * Encuentra todas las dispensaciones de prescripción activas
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('pd')
            ->where('pd.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pd.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
