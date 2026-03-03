<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BranchPayer;
use App\Entity\Tenant\BudgetSurgeryFeePrice;
use App\Entity\Tenant\MedicalService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BudgetSurgeryFeePrice>
 */
class BudgetSurgeryFeePriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BudgetSurgeryFeePrice::class);
    }

    public function findActivePrice(
        MedicalService $service,
        ?BranchPayer $branchPayer = null
    ): ?BudgetSurgeryFeePrice {
        $qb = $this->createQueryBuilder('p')
            ->where('p.medicalService = :service')
            ->andWhere('p.isActive = true')
            ->setParameter('service', $service)
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
