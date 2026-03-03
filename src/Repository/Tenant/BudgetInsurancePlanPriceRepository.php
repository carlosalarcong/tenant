<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BranchPayer;
use App\Entity\Tenant\BudgetInsurancePlanPrice;
use App\Entity\Tenant\InsurancePlan;
use App\Entity\Tenant\MedicalService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BudgetInsurancePlanPrice>
 */
class BudgetInsurancePlanPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BudgetInsurancePlanPrice::class);
    }

    public function findActivePrice(
        MedicalService $service,
        InsurancePlan $plan,
        ?BranchPayer $branchPayer = null
    ): ?BudgetInsurancePlanPrice {
        $qb = $this->createQueryBuilder('p')
            ->where('p.medicalService = :service')
            ->andWhere('p.insurancePlan = :plan')
            ->andWhere('p.isActive = true')
            ->setParameter('service', $service)
            ->setParameter('plan', $plan)
            ->orderBy('p.effectiveDate', 'DESC')
            ->setMaxResults(1);

        if ($branchPayer) {
            $qb->andWhere('p.branchPayer = :bp')->setParameter('bp', $branchPayer);
        } else {
            $qb->andWhere('p.branchPayer IS NULL');
        }

        return $qb->getQuery()->getOneOrNullResult();
    }
}
