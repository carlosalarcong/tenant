<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BranchPayer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BranchPayer>
 */
class BranchPayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BranchPayer::class);
    }

    public function findByBranch(int $branchId): array
    {
        return $this->createQueryBuilder('bp')
            ->where('bp.branch = :branchId')
            ->setParameter('branchId', $branchId)
            ->getQuery()
            ->getResult();
    }

    public function findByPayer(int $payerId): array
    {
        return $this->createQueryBuilder('bp')
            ->where('bp.payer = :payerId')
            ->setParameter('payerId', $payerId)
            ->getQuery()
            ->getResult();
    }
}
