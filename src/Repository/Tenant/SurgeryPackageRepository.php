<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\SurgeryPackage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SurgeryPackage>
 */
class SurgeryPackageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurgeryPackage::class);
    }
}
