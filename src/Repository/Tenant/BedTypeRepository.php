<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BedType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BedType>
 */
class BedTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BedType::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('bt')
            ->where('bt.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('bt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findRequiringSpecialCare(): array
    {
        return $this->createQueryBuilder('bt')
            ->where('bt.requiresSpecialCare = :requires')
            ->andWhere('bt.isActive = :active')
            ->setParameter('requires', true)
            ->setParameter('active', true)
            ->orderBy('bt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
