<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BillingItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BillingItem>
 */
class BillingItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BillingItem::class);
    }

    /**
     * Encuentra todos los items activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('bi')
            ->where('bi.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('bi.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
