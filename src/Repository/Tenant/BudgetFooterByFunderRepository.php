<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BudgetFooterByFunder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BudgetFooterByFunder>
 */
class BudgetFooterByFunderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BudgetFooterByFunder::class);
    }

    /**
     * @return BudgetFooterByFunder[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('bfbf')
            ->where('bfbf.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('bfbf.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return BudgetFooterByFunder[]
     */
    public function findByBudgetFooter(int $budgetFooterId): array
    {
        return $this->createQueryBuilder('bfbf')
            ->where('bfbf.budgetFooter = :budgetFooterId')
            ->setParameter('budgetFooterId', $budgetFooterId)
            ->orderBy('bfbf.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
