<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\CareCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CareCategory>
 */
class CareCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CareCategory::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('cc')
            ->where('cc.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('cc.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
