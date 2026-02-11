<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ArticleOutflowType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticleOutflowType>
 */
class ArticleOutflowTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleOutflowType::class);
    }

    /**
     * Encuentra todos los tipos de egreso de artículos activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('aot')
            ->where('aot.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('aot.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
