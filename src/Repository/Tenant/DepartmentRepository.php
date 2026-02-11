<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Department;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Department>
 */
class DepartmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Department::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('d.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByBranch(int $branchId): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.branch = :branch')
            ->andWhere('d.isActive = :active')
            ->setParameter('branch', $branchId)
            ->setParameter('active', true)
            ->orderBy('d.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCode(string $code): ?Department
    {
        return $this->createQueryBuilder('d')
            ->where('d.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
