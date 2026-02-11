<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Article>
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Encuentra todos los artículos activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra artículo por código
     */
    public function findByCode(string $code): ?Article
    {
        return $this->createQueryBuilder('a')
            ->where('a.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Encuentra artículos por tipo
     */
    public function findByArticleType(int $articleTypeId): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.articleType = :articleTypeId')
            ->andWhere('a.isActive = :active')
            ->setParameter('articleTypeId', $articleTypeId)
            ->setParameter('active', true)
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra artículos controlados activos
     */
    public function findControlled(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isControlled = :controlled')
            ->andWhere('a.isActive = :active')
            ->setParameter('controlled', true)
            ->setParameter('active', true)
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra artículos críticos activos
     */
    public function findCritical(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isCritical = :critical')
            ->andWhere('a.isActive = :active')
            ->setParameter('critical', true)
            ->setParameter('active', true)
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
