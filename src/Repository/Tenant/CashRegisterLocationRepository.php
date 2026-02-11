<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\CashRegisterLocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CashRegisterLocation>
 */
class CashRegisterLocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CashRegisterLocation::class);
    }

    /**
     * Encuentra todas las ubicaciones de caja activas
     *
     * @return CashRegisterLocation[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('crl')
            ->where('crl.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('crl.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
