<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Room;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Room>
 */
class RoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('r.roomNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByClinic(int $clinicId): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.clinic = :clinicId')
            ->andWhere('r.isActive = :active')
            ->setParameter('clinicId', $clinicId)
            ->setParameter('active', true)
            ->orderBy('r.roomNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByRoomType(string $roomType): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.roomType = :roomType')
            ->andWhere('r.isActive = :active')
            ->setParameter('roomType', $roomType)
            ->setParameter('active', true)
            ->orderBy('r.roomNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAvailableRooms(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.isOccupied = :occupied')
            ->andWhere('r.isActive = :active')
            ->setParameter('occupied', false)
            ->setParameter('active', true)
            ->orderBy('r.roomNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByRoomNumber(string $roomNumber): ?Room
    {
        return $this->createQueryBuilder('r')
            ->where('r.roomNumber = :roomNumber')
            ->setParameter('roomNumber', $roomNumber)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
