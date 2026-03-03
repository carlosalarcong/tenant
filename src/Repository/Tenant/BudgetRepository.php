<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Budget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use \DateTimeInterface;

/**
 * @extends ServiceEntityRepository<Budget>
 */
class BudgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Budget::class);
    }

    public function getNextNumber(): int
    {
        $result = $this->createQueryBuilder('b')
            ->select('MAX(b.number)')
            ->getQuery()
            ->getSingleScalarResult();

        return ($result ?? 0) + 1;
    }

    public function findByFilters(
        ?DateTimeInterface $dateFrom,
        ?DateTimeInterface $dateTo,
        ?int $memberId,
        ?string $status,
        ?int $branchId = null
    ): array {
        $qb = $this->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'DESC');

        if ($dateFrom) {
            $qb->andWhere('b.createdAt >= :from')
               ->setParameter('from', $dateFrom);
        }
        if ($dateTo) {
            $end = (clone $dateTo)->setTime(23, 59, 59);
            $qb->andWhere('b.createdAt <= :to')
               ->setParameter('to', $end);
        }
        if ($memberId) {
            $qb->andWhere('b.member = :member')
               ->setParameter('member', $memberId);
        }
        if ($status) {
            $qb->andWhere('b.status = :status')
               ->setParameter('status', $status);
        }
        if ($branchId) {
            $qb->andWhere('b.branch = :branch')
               ->setParameter('branch', $branchId);
        }

        return $qb->getQuery()->getResult();
    }
}
