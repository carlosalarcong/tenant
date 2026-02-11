<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ArticleSupplier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticleSupplier>
 */
class ArticleSupplierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleSupplier::class);
    }

    /**
     * Encuentra todos los proveedores de artículos activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('as2')
            ->where('as2.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('as2.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra proveedores por artículo
     */
    public function findByArticle(int $articleId): array
    {
        return $this->createQueryBuilder('as2')
            ->where('as2.article = :articleId')
            ->andWhere('as2.isActive = :active')
            ->setParameter('articleId', $articleId)
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }
}
