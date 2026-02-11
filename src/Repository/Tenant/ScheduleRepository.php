<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Schedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Schedule>
 */
class ScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Schedule::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('s.scheduleDate', 'DESC')
            ->addOrderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByProfessional(int $professionalId): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.professional = :professionalId')
            ->andWhere('s.isActive = :active')
            ->setParameter('professionalId', $professionalId)
            ->setParameter('active', true)
            ->orderBy('s.scheduleDate', 'DESC')
            ->addOrderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByProfessionalAndDate(int $professionalId, \DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.professional = :professionalId')
            ->andWhere('s.scheduleDate = :date')
            ->andWhere('s.isActive = :active')
            ->setParameter('professionalId', $professionalId)
            ->setParameter('date', $date)
            ->setParameter('active', true)
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.scheduleDate BETWEEN :startDate AND :endDate')
            ->andWhere('s.isActive = :active')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('active', true)
            ->orderBy('s.scheduleDate', 'ASC')
            ->addOrderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAvailableSlots(int $professionalId, \DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.professional = :professionalId')
            ->andWhere('s.scheduleDate = :date')
            ->andWhere('s.isAvailable = :available')
            ->andWhere('s.isActive = :active')
            ->setParameter('professionalId', $professionalId)
            ->setParameter('date', $date)
            ->setParameter('available', true)
            ->setParameter('active', true)
            ->orderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
