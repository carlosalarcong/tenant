<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\CancellationType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CancellationType>
 */
class CancellationTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CancellationType::class);
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

    public function findAllowingRefund(): array
    {
        return $this->createQueryBuilder('ct')
            ->where('ct.allowsRefund = :allows')
            ->andWhere('ct.isActive = :active')
            ->setParameter('allows', true)
            ->setParameter('active', true)
            ->orderBy('ct.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
