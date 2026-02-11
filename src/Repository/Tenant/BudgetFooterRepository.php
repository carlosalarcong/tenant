<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BudgetFooter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BudgetFooter>
 */
class BudgetFooterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BudgetFooter::class);
    }

    /**
     * @return BudgetFooter[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('bf')
            ->where('bf.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('bf.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
