<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\CareIntervention;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CareIntervention>
 */
class CareInterventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CareIntervention::class);
    }

    /**
     * @return CareIntervention[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('c.description', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return CareIntervention[]
     */
    public function findByCategory(int $categoryId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.category = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->getQuery()
            ->getResult();
    }

    public function searchActiveByTerm(string $term, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('c')
            ->andWhere('c.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('c.description', 'ASC')
            ->setMaxResults($limit);

        if ($term !== '') {
            $qb
                ->andWhere('LOWER(c.description) LIKE :term')
                ->setParameter('term', '%' . mb_strtolower($term) . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
