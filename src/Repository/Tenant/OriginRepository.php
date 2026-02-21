<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Origin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Origin>
 */
class OriginRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Origin::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('o.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    public function findActiveChoices(): array
    {
        /** @var array<int, array{id:int,name:string}> $rows */
        $rows = $this->createQueryBuilder('o')
            ->select('o.id AS id', 'o.name AS name')
            ->where('o.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('o.name', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }
}
