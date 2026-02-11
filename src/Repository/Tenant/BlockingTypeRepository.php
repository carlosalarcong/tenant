<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BlockingType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlockingType>
 */
class BlockingTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlockingType::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('bt')
            ->where('bt.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('bt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findRequiringApproval(): array
    {
        return $this->createQueryBuilder('bt')
            ->where('bt.requiresApproval = :requires')
            ->andWhere('bt.isActive = :active')
            ->setParameter('requires', true)
            ->setParameter('active', true)
            ->orderBy('bt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
