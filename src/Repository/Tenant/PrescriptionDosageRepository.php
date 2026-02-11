<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\PrescriptionDosage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrescriptionDosage>
 */
class PrescriptionDosageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrescriptionDosage::class);
    }

    /**
     * Encuentra todas las dosificaciones de prescripción activas
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('pd')
            ->where('pd.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pd.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
