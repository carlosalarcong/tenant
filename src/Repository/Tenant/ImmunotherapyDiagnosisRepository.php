<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ImmunotherapyDiagnosis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ImmunotherapyDiagnosis>
 */
class ImmunotherapyDiagnosisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImmunotherapyDiagnosis::class);
    }

    /**
     * @return ImmunotherapyDiagnosis[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('id')
            ->where('id.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('id.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
