<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\PathologyArticle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PathologyArticle>
 */
class PathologyArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PathologyArticle::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('pa')
            ->where('pa.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pa.articleName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByGESPathology(int $gesPathologyId): array
    {
        return $this->createQueryBuilder('pa')
            ->where('pa.gesPathology = :gesPathologyId')
            ->andWhere('pa.isActive = :active')
            ->setParameter('gesPathologyId', $gesPathologyId)
            ->setParameter('active', true)
            ->orderBy('pa.articleName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findRequiredArticles(int $gesPathologyId): array
    {
        return $this->createQueryBuilder('pa')
            ->where('pa.gesPathology = :gesPathologyId')
            ->andWhere('pa.isRequired = :required')
            ->andWhere('pa.isActive = :active')
            ->setParameter('gesPathologyId', $gesPathologyId)
            ->setParameter('required', true)
            ->setParameter('active', true)
            ->orderBy('pa.articleName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByArticleCode(string $articleCode): array
    {
        return $this->createQueryBuilder('pa')
            ->where('pa.articleCode = :articleCode')
            ->andWhere('pa.isActive = :active')
            ->setParameter('articleCode', $articleCode)
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }
}
