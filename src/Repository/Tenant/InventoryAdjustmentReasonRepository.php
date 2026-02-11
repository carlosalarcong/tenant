<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\InventoryAdjustmentReason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InventoryAdjustmentReason>
 */
class InventoryAdjustmentReasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InventoryAdjustmentReason::class);
    }

    /**
     * Encuentra todos los motivos de ajuste de inventario activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('iar')
            ->where('iar.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('iar.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
