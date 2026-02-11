<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\SettlementBase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SettlementBase>
 */
class SettlementBaseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SettlementBase::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('sb')
            ->where('sb.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('sb.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
