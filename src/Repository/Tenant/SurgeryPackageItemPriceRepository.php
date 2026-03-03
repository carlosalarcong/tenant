<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BranchPayer;
use App\Entity\Tenant\SurgeryPackageItem;
use App\Entity\Tenant\SurgeryPackageItemPrice;
use App\Entity\Tenant\SurgeryPackagePlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SurgeryPackageItemPrice>
 */
class SurgeryPackageItemPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurgeryPackageItemPrice::class);
    }

    public function findActivePrice(
        SurgeryPackageItem $item,
        SurgeryPackagePlan $plan,
        ?BranchPayer $branchPayer = null
    ): ?SurgeryPackageItemPrice {
        $qb = $this->createQueryBuilder('p')
            ->where('p.surgeryPackageItem = :item')
            ->andWhere('p.surgeryPackagePlan = :plan')
            ->andWhere('p.isActive = true')
            ->setParameter('item', $item)
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
