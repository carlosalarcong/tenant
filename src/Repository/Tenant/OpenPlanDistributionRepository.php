<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\OpenPlanDistribution;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OpenPlanDistribution>
 */
class OpenPlanDistributionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OpenPlanDistribution::class);
    }
}
