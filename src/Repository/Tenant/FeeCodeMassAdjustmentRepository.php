<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\FeeCodeMassAdjustment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FeeCodeMassAdjustment>
 */
class FeeCodeMassAdjustmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeeCodeMassAdjustment::class);
    }
}
