<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Professional;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Professional>
 */
class ProfessionalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Professional::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.lastName', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySpecialty(int $specialtyId): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.specialty = :specialtyId')
            ->andWhere('p.isActive = :active')
            ->setParameter('specialtyId', $specialtyId)
            ->setParameter('active', true)
            ->orderBy('p.lastName', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByProfessionalType(string $professionalType): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.professionalType = :professionalType')
            ->andWhere('p.isActive = :active')
            ->setParameter('professionalType', $professionalType)
            ->setParameter('active', true)
            ->orderBy('p.lastName', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByRut(string $rut): ?Professional
    {
        return $this->createQueryBuilder('p')
            ->where('p.rut = :rut')
            ->setParameter('rut', $rut)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByCode(string $code): ?Professional
    {
        return $this->createQueryBuilder('p')
            ->where('p.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findDoctors(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.professionalType = :type')
            ->andWhere('p.isActive = :active')
            ->setParameter('type', 'doctor')
            ->setParameter('active', true)
            ->orderBy('p.lastName', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAvailableProfessionals(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isAvailableForScheduling = :available')
            ->andWhere('p.isActive = :active')
            ->setParameter('available', true)
            ->setParameter('active', true)
            ->orderBy('p.lastName', 'ASC')
            ->addOrderBy('p.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
