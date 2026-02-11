<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Warehouse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Warehouse>
 */
class WarehouseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Warehouse::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('w')
            ->where('w.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('w.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPharmacies(): array
    {
        return $this->createQueryBuilder('w')
            ->where('w.isPharmacy = :isPharmacy')
            ->andWhere('w.isActive = :active')
            ->setParameter('isPharmacy', true)
            ->setParameter('active', true)
            ->orderBy('w.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByService(int $serviceId): array
    {
        return $this->createQueryBuilder('w')
            ->where('w.service = :serviceId')
            ->andWhere('w.isActive = :active')
            ->setParameter('serviceId', $serviceId)
            ->setParameter('active', true)
            ->orderBy('w.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCode(string $code): ?Warehouse
    {
        return $this->createQueryBuilder('w')
            ->where('w.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
