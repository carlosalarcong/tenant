<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\DiagnosisStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DiagnosisStatus>
 */
class DiagnosisStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiagnosisStatus::class);
    }

    /**
     * @return DiagnosisStatus[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('ds')
            ->where('ds.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('ds.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
