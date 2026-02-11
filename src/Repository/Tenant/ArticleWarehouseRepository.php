<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ArticleWarehouse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticleWarehouse>
 */
class ArticleWarehouseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleWarehouse::class);
    }

    /**
     * Encuentra todas las asignaciones activas
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('aw')
            ->where('aw.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('aw.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra bodegas por artículo
     */
    public function findByArticle(int $articleId): array
    {
        return $this->createQueryBuilder('aw')
            ->where('aw.article = :articleId')
            ->andWhere('aw.isActive = :active')
            ->setParameter('articleId', $articleId)
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra artículos por bodega
     */
    public function findByWarehouse(int $warehouseId): array
    {
        return $this->createQueryBuilder('aw')
            ->where('aw.warehouse = :warehouseId')
            ->andWhere('aw.isActive = :active')
            ->setParameter('warehouseId', $warehouseId)
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra artículos críticos
     */
    public function findCritical(): array
    {
        return $this->createQueryBuilder('aw')
            ->where('aw.isCritical = :critical')
            ->andWhere('aw.isActive = :active')
            ->setParameter('critical', true)
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }
}
