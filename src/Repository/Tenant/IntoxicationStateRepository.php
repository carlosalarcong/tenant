<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\IntoxicationState;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IntoxicationState>
 */
class IntoxicationStateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IntoxicationState::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('is2')
            ->where('is2.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('is2.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
