<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\SurgeryPackagePrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SurgeryPackagePrice>
 */
class SurgeryPackagePriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurgeryPackagePrice::class);
    }
}
