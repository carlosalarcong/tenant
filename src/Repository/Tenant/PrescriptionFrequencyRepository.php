<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\PrescriptionFrequency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrescriptionFrequency>
 */
class PrescriptionFrequencyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrescriptionFrequency::class);
    }

    /**
     * Encuentra todas las frecuencias de prescripción activas
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('pf')
            ->where('pf.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pf.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
