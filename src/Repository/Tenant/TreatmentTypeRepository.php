<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\TreatmentType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TreatmentType>
 */
class TreatmentTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TreatmentType::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('tt')
            ->where('tt.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('tt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findRequiringSpecialist(): array
    {
        return $this->createQueryBuilder('tt')
            ->where('tt.requiresSpecialist = :requires')
            ->andWhere('tt.isActive = :active')
            ->setParameter('requires', true)
            ->setParameter('active', true)
            ->orderBy('tt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
