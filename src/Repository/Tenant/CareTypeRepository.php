<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\CareType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CareType>
 */
class CareTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CareType::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findFirstActive(): ?CareType
    {
        return $this->createQueryBuilder('c')
            ->where('c.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function hasAnyActive(): bool
    {
        /** @var array{id:int}|null $row */
        $row = $this->createQueryBuilder('c')
            ->select('c.id AS id')
            ->where('c.isActive = :active')
            ->setParameter('active', true)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $row !== null;
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    public function findActiveChoices(): array
    {
        /** @var array<int, array{id:int,name:string}> $rows */
        $rows = $this->createQueryBuilder('c')
            ->select('c.id AS id', 'c.name AS name')
            ->where('c.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }
}
