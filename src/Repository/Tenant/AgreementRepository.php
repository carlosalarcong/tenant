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
}

