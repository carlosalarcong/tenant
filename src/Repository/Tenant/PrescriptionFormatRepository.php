<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\PrescriptionFormat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrescriptionFormat>
 */
class PrescriptionFormatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrescriptionFormat::class);
    }

    /**
     * Encuentra todos los formatos de prescripción activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('pf')
            ->where('pf.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pf.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
