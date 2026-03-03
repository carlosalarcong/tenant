<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\OpenPlanPrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OpenPlanPrice>
 */
class OpenPlanPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OpenPlanPrice::class);
    }
}
