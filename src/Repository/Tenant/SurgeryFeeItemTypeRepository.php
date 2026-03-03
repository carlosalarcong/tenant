<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\SurgeryFeeItemType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SurgeryFeeItemType>
 */
class SurgeryFeeItemTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurgeryFeeItemType::class);
    }
}
