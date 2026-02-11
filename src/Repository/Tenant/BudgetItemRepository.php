<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BudgetItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BudgetItem>
 */
class BudgetItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BudgetItem::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('bi')
            ->where('bi.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('bi.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('bi')
            ->where('bi.category = :category')
            ->andWhere('bi.isActive = :active')
            ->setParameter('category', $category)
            ->setParameter('active', true)
            ->orderBy('bi.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCode(string $code): ?BudgetItem
    {
        return $this->createQueryBuilder('bi')
            ->where('bi.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
