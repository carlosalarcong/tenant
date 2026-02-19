<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Bed;
use App\Entity\Tenant\MedicalServiceBedType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bed>
 */
class BedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bed::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('b.bedNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByBedType(int $bedTypeId): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.bedType = :bedTypeId')
            ->andWhere('b.isActive = :active')
            ->setParameter('bedTypeId', $bedTypeId)
            ->setParameter('active', true)
            ->orderBy('b.bedNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByRoom(int $roomId): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.room = :roomId')
            ->andWhere('b.isActive = :active')
            ->setParameter('roomId', $roomId)
            ->setParameter('active', true)
            ->orderBy('b.bedNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAvailableBeds(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.status = :status')
            ->andWhere('b.isActive = :active')
            ->setParameter('status', 'available')
            ->setParameter('active', true)
            ->orderBy('b.bedNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOccupiedBeds(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.status = :status')
            ->andWhere('b.isActive = :active')
            ->setParameter('status', 'occupied')
            ->setParameter('active', true)
            ->orderBy('b.bedNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByBedNumber(string $bedNumber): ?Bed
    {
        return $this->createQueryBuilder('b')
            ->where('b.bedNumber = :bedNumber')
            ->setParameter('bedNumber', $bedNumber)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByFloor(string $floor): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.floor = :floor')
            ->andWhere('b.isActive = :active')
            ->setParameter('floor', $floor)
            ->setParameter('active', true)
            ->orderBy('b.bedNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveBedsForMedicalService(int $medicalServiceId): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.room', 'r')
            ->innerJoin(
                MedicalServiceBedType::class,
                'msbt',
                'WITH',
                'msbt.bedType = b.bedType AND msbt.medicalService = :medicalServiceId AND msbt.isActive = :active'
            )
            ->where('b.isActive = :active')
            ->setParameter('medicalServiceId', $medicalServiceId)
            ->setParameter('active', true)
            ->addOrderBy('r.roomNumber', 'ASC')
            ->addOrderBy('b.bedNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countAvailableBedsForMedicalService(int $medicalServiceId): int
    {
        return (int) $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->innerJoin(
                MedicalServiceBedType::class,
                'msbt',
                'WITH',
                'msbt.bedType = b.bedType AND msbt.medicalService = :medicalServiceId AND msbt.isActive = :active'
            )
            ->where('b.isActive = :active')
            ->andWhere('b.status = :status')
            ->setParameter('medicalServiceId', $medicalServiceId)
            ->setParameter('active', true)
            ->setParameter('status', 'available')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findOneActiveById(int $bedId): ?Bed
    {
        return $this->createQueryBuilder('b')
            ->where('b.id = :bedId')
            ->andWhere('b.isActive = :active')
            ->setParameter('bedId', $bedId)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
