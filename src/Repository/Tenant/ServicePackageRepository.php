<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ServicePackage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ServicePackage>
 */
class ServicePackageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServicePackage::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('sp')
            ->where('sp.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('sp.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCode(string $code): ?ServicePackage
    {
        return $this->createQueryBuilder('sp')
            ->where('sp.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
