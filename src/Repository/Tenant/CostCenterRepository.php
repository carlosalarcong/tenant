<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\CostCenter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CostCenter>
 */
class CostCenterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CostCenter::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('cc')
            ->where('cc.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('cc.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCode(string $code): ?CostCenter
    {
        return $this->createQueryBuilder('cc')
            ->where('cc.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
