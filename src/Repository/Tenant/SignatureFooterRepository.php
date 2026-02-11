<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\SignatureFooter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SignatureFooter>
 */
class SignatureFooterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SignatureFooter::class);
    }

    /**
     * Encuentra todos los pies de firma activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('sf')
            ->where('sf.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('sf.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra pies de firma por sucursal
     */
    public function findByBranch(int $branchId): array
    {
        return $this->createQueryBuilder('sf')
            ->where('sf.branch = :branchId')
            ->andWhere('sf.isActive = :active')
            ->setParameter('branchId', $branchId)
            ->setParameter('active', true)
            ->orderBy('sf.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
