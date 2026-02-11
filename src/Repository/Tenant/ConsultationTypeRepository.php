<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ConsultationType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConsultationType>
 */
class ConsultationTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsultationType::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('ct')
            ->where('ct.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('ct.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findEmergencyTypes(): array
    {
        return $this->createQueryBuilder('ct')
            ->where('ct.isEmergency = :emergency')
            ->andWhere('ct.isActive = :active')
            ->setParameter('emergency', true)
            ->setParameter('active', true)
            ->orderBy('ct.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
