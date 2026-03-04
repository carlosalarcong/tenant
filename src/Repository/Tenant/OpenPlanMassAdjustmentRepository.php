<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\OpenPlanMassAdjustment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OpenPlanMassAdjustment>
 */
class OpenPlanMassAdjustmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OpenPlanMassAdjustment::class);
    }
}
