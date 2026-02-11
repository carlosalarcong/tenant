<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\DailyUF;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DailyUF>
 */
class DailyUFRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyUF::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('duf')
            ->where('duf.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('duf.date', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByDate(\DateTimeInterface $date): ?DailyUF
    {
        return $this->createQueryBuilder('duf')
            ->where('duf.date = :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByDateRange(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return $this->createQueryBuilder('duf')
            ->where('duf.date BETWEEN :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('duf.date', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
