<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\InsurancePlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InsurancePlan>
 */
class InsurancePlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InsurancePlan::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    public function findActiveChoices(): array
    {
        /** @var array<int, array{id:int,name:string}> $rows */
        $rows = $this->createQueryBuilder('p')
            ->select('p.id AS id', 'p.name AS name')
            ->where('p.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }
}
