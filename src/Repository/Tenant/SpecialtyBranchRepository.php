<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\SpecialtyBranch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SpecialtyBranch>
 */
class SpecialtyBranchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SpecialtyBranch::class);
    }

    public function findBySpecialty(int $specialtyId): array
    {
        return $this->createQueryBuilder('sb')
            ->where('sb.specialty = :specialtyId')
            ->setParameter('specialtyId', $specialtyId)
            ->getQuery()
            ->getResult();
    }

    public function findByBranch(int $branchId): array
    {
        return $this->createQueryBuilder('sb')
            ->where('sb.branch = :branchId')
            ->setParameter('branchId', $branchId)
            ->getQuery()
            ->getResult();
    }
}
