<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ClinicalActionCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClinicalActionCategory>
 */
class ClinicalActionCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClinicalActionCategory::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('cac')
            ->where('cac.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('cac.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
