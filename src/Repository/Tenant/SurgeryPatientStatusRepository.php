<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\SurgeryPatientStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SurgeryPatientStatus>
 */
class SurgeryPatientStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurgeryPatientStatus::class);
    }
}
