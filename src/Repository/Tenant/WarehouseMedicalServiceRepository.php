<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\WarehouseMedicalService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WarehouseMedicalService>
 */
class WarehouseMedicalServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WarehouseMedicalService::class);
    }

    public function findByWarehouse(int $warehouseId): array
    {
        return $this->createQueryBuilder('wms')
            ->where('wms.warehouse = :warehouseId')
            ->setParameter('warehouseId', $warehouseId)
            ->getQuery()
            ->getResult();
    }

    public function findByMedicalService(int $medicalServiceId): array
    {
        return $this->createQueryBuilder('wms')
            ->where('wms.medicalService = :medicalServiceId')
            ->setParameter('medicalServiceId', $medicalServiceId)
            ->getQuery()
            ->getResult();
    }
}
