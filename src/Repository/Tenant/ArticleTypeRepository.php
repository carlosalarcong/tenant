<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ArticleType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticleType>
 */
class ArticleTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleType::class);
    }

    /**
     * Encuentra todos los tipos de artículo activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('at')
            ->where('at.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('at.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra tipos de artículo por bodega
     */
    public function findByWarehouse(int $warehouseId): array
    {
        return $this->createQueryBuilder('at')
            ->where('at.warehouse = :warehouseId')
            ->andWhere('at.isActive = :active')
            ->setParameter('warehouseId', $warehouseId)
            ->setParameter('active', true)
            ->orderBy('at.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
