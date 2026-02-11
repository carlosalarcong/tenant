<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Service>
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByServiceType(int $serviceTypeId): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.serviceType = :serviceTypeId')
            ->andWhere('s.isActive = :active')
            ->setParameter('serviceTypeId', $serviceTypeId)
            ->setParameter('active', true)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySpecialty(int $specialtyId): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.specialty = :specialtyId')
            ->andWhere('s.isActive = :active')
            ->setParameter('specialtyId', $specialtyId)
            ->setParameter('active', true)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCode(string $code): ?Service
    {
        return $this->createQueryBuilder('s')
            ->where('s.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAvailableServices(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.isAvailable = :available')
            ->andWhere('s.isActive = :active')
            ->setParameter('available', true)
            ->setParameter('active', true)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
