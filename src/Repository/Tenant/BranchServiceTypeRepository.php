<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BranchServiceType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BranchServiceType>
 */
class BranchServiceTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BranchServiceType::class);
    }

    public function findByBranch(int $branchId): array
    {
        return $this->createQueryBuilder('bst')
            ->where('bst.branch = :branchId')
            ->setParameter('branchId', $branchId)
            ->getQuery()
            ->getResult();
    }

    public function findByServiceType(int $serviceTypeId): array
    {
        return $this->createQueryBuilder('bst')
            ->where('bst.serviceType = :serviceTypeId')
            ->setParameter('serviceTypeId', $serviceTypeId)
            ->getQuery()
            ->getResult();
    }
}
