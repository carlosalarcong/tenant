<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BranchPayer;
use App\Entity\Tenant\FeeCodePrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FeeCodePrice>
 */
class FeeCodePriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeeCodePrice::class);
    }

    public function findLatestByBranchPayer(BranchPayer $bp): array
    {
        $rows = $this->createQueryBuilder('fcp')
            ->leftJoin('fcp.feeCode', 'fc')
            ->addSelect('fc')
            ->where('fcp.branchPayer = :branchPayer')
            ->andWhere(
                'fcp.effectiveDate = (
                    SELECT MAX(innerFcp.effectiveDate)
                    FROM ' . FeeCodePrice::class . ' innerFcp
                    WHERE innerFcp.branchPayer = :branchPayer
                    AND innerFcp.feeCode = fcp.feeCode
                )'
            )
            ->setParameter('branchPayer', $bp)
            ->orderBy('fc.id', 'ASC')
            ->addOrderBy('fcp.id', 'DESC')
            ->getQuery()
            ->getResult();

        $latest = [];
        foreach ($rows as $row) {
            $feeCodeId = $row->getFeeCode()?->getId();
            if ($feeCodeId === null || isset($latest[$feeCodeId])) {
                continue;
            }

            $latest[$feeCodeId] = $row;
        }

        return array_values($latest);
    }
}
