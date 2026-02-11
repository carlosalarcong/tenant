<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\SurgeryPatientStatusConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SurgeryPatientStatusConfig>
 */
class SurgeryPatientStatusConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurgeryPatientStatusConfig::class);
    }
}
