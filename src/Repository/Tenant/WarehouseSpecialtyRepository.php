<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\WarehouseSpecialty;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WarehouseSpecialty>
 */
class WarehouseSpecialtyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WarehouseSpecialty::class);
    }

    /**
     * Encuentra todas las especialidades por bodega activas
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('ws')
            ->where('ws.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('ws.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra especialidades por bodega
     */
    public function findByWarehouse(int $warehouseId): array
    {
        return $this->createQueryBuilder('ws')
            ->where('ws.warehouse = :warehouseId')
            ->andWhere('ws.isActive = :active')
            ->setParameter('warehouseId', $warehouseId)
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra bodegas por especialidad
     */
    public function findBySpecialty(int $specialtyId): array
    {
        return $this->createQueryBuilder('ws')
            ->where('ws.specialty = :specialtyId')
            ->andWhere('ws.isActive = :active')
            ->setParameter('specialtyId', $specialtyId)
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }
}
