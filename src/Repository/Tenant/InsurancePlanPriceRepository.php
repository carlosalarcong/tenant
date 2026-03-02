<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\InsurancePlanPrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InsurancePlanPrice>
 */
class InsurancePlanPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InsurancePlanPrice::class);
    }
}
