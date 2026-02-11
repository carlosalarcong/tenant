<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\SurgerySuspensionCause;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SurgerySuspensionCause>
 */
class SurgerySuspensionCauseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurgerySuspensionCause::class);
    }

    /**
     * @return SurgerySuspensionCause[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
