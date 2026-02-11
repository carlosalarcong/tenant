<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\MedicalService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MedicalService>
 */
class MedicalServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MedicalService::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('ms')
            ->where('ms.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('ms.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByDepartment(int $departmentId): array
    {
        return $this->createQueryBuilder('ms')
            ->where('ms.department = :department')
            ->andWhere('ms.isActive = :active')
            ->setParameter('department', $departmentId)
            ->setParameter('active', true)
            ->orderBy('ms.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCode(string $code): ?MedicalService
    {
        return $this->createQueryBuilder('ms')
            ->where('ms.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
