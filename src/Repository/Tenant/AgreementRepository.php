<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Agreement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Agreement>
 */
class AgreementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agreement::class);
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    public function findActiveChoicesByPayer(int $payerId): array
    {
        /** @var array<int, array{id:int,name:string}> $rows */
        $rows = $this->createQueryBuilder('a')
            ->select('a.id AS id', 'a.name AS name')
            ->where('a.isActive = :active')
            ->andWhere('a.payer = :payerId')
            ->setParameter('active', true)
            ->setParameter('payerId', $payerId)
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }

    public function isActiveForPayer(int $agreementId, int $payerId): bool
    {
        if ($agreementId <= 0 || $payerId <= 0) {
            return false;
        }

        /** @var array{id:int}|null $row */
        $row = $this->createQueryBuilder('a')
            ->select('a.id AS id')
            ->where('a.id = :agreementId')
            ->andWhere('a.isActive = :active')
            ->andWhere('a.payer = :payerId')
            ->setParameter('agreementId', $agreementId)
            ->setParameter('active', true)
            ->setParameter('payerId', $payerId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $row !== null;
    }
}
