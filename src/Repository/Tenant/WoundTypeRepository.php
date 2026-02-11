<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\WoundType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WoundType>
 */
class WoundTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WoundType::class);
    }

    /**
     * @return WoundType[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('wt')
            ->where('wt.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('wt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
