<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BedAssignmentStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BedAssignmentStatus>
 */
class BedAssignmentStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BedAssignmentStatus::class);
    }
}
