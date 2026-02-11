<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\GratuityReason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GratuityReason>
 */
class GratuityReasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GratuityReason::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('gr')
            ->where('gr.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('gr.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
