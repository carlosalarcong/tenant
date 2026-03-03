<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BudgetObservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BudgetObservation>
 */
class BudgetObservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BudgetObservation::class);
    }
}
