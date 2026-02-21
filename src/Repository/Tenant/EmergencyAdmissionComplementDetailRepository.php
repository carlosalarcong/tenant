<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\EmergencyAdmissionComplementDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmergencyAdmissionComplementDetail>
 */
class EmergencyAdmissionComplementDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmergencyAdmissionComplementDetail::class);
    }
}
