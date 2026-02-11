<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\SurgeryCancellationReason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SurgeryCancellationReason>
 */
class SurgeryCancellationReasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurgeryCancellationReason::class);
    }

    /**
     * @return SurgeryCancellationReason[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('scr')
            ->where('scr.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('scr.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
