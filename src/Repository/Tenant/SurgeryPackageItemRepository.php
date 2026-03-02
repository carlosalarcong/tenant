<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\SurgeryPackageItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SurgeryPackageItem>
 */
class SurgeryPackageItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurgeryPackageItem::class);
    }
}
