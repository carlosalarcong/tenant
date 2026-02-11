<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\NutritionistIndexClassification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NutritionistIndexClassification>
 */
class NutritionistIndexClassificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NutritionistIndexClassification::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('nic')
            ->where('nic.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('nic.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
