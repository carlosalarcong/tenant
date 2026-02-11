<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Bed;
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

    /**
     * @return array<int, array{id:int,name:string}>
     */
    public function findActiveChoicesByService(?int $serviceId = null): array
    {
        $qb = $this->createQueryBuilder('b')
            ->select('b.id AS id', "CONCAT('Cama ', b.bedNumber) AS name")
            ->where('b.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('b.bedNumber', 'ASC');

        // TODO: filtrar por servicio cuando exista relación Bed -> Service en el modelo.
        if ($serviceId !== null && $serviceId > 0) {
            // Sin filtro por ahora
        }

        /** @var array<int, array{id:int,name:string}> $rows */
        $rows = $qb->getQuery()->getArrayResult();

        return $rows;
    }
}
