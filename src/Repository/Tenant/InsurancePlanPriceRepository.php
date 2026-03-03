<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BranchPayer;
use App\Entity\Tenant\InsurancePlan;
use App\Entity\Tenant\InsurancePlanPrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InsurancePlanPrice>
 */
class InsurancePlanPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InsurancePlanPrice::class);
    }

    public function findActiveByPlanAndBranchPayer(InsurancePlan $plan, BranchPayer $bp): array
    {
        return $this->createQueryBuilder('ipp')
            ->where('ipp.isActive = :active')
            ->andWhere('ipp.branchPayer = :branchPayer')
            ->andWhere('ipp.insurancePlan = :insurancePlan')
            ->setParameter('active', true)
            ->setParameter('branchPayer', $bp)
            ->setParameter('insurancePlan', $plan)
            ->orderBy('ipp.effectiveDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
