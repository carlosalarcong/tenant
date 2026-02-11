<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BudgetFunderFooter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BudgetFunderFooter>
 */
class BudgetFunderFooterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BudgetFunderFooter::class);
    }

    /**
     * @return BudgetFunderFooter[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('bff')
            ->where('bff.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('bff.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return BudgetFunderFooter[]
     */
    public function findByBudgetFooter(int $budgetFooterId): array
    {
        return $this->createQueryBuilder('bff')
            ->where('bff.budgetFooter = :budgetFooterId')
            ->setParameter('budgetFooterId', $budgetFooterId)
            ->orderBy('bff.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
