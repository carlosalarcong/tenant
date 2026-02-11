<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\NutritionalDiagnosis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NutritionalDiagnosis>
 */
class NutritionalDiagnosisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NutritionalDiagnosis::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('nd')
            ->where('nd.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('nd.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
