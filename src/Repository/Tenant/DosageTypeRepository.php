<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\DosageType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DosageType>
 */
class DosageTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DosageType::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('dt')
            ->where('dt.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('dt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
