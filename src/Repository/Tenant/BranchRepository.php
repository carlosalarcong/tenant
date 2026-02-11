<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Branch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Branch>
 */
class BranchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Branch::class);
    }

    /**
     * Find all active branches
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find branch by code
     */
    public function findByCode(string $code): ?Branch
    {
        return $this->createQueryBuilder('b')
            ->where('b.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find branches by city
     */
    public function findByCity(string $city): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.city = :city')
            ->andWhere('b.isActive = :active')
            ->setParameter('city', $city)
            ->setParameter('active', true)
            ->orderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find branches by region
     */
    public function findByRegion(string $region): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.region = :region')
            ->andWhere('b.isActive = :active')
            ->setParameter('region', $region)
            ->setParameter('active', true)
            ->orderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count total branches
     */
    public function countTotal(): int
    {
        return (int) $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count active branches
     */
    public function countActive(): int
    {
        return (int) $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('b.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
