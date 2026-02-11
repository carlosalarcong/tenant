<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\PrescriptionType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrescriptionType>
 */
class PrescriptionTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrescriptionType::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('pt')
            ->where('pt.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
