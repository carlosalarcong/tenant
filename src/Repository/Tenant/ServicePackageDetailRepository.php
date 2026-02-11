<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ServicePackageDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ServicePackageDetail>
 */
class ServicePackageDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServicePackageDetail::class);
    }

    public function findByServicePackage(int $servicePackageId): array
    {
        return $this->createQueryBuilder('spd')
            ->where('spd.servicePackage = :servicePackageId')
            ->setParameter('servicePackageId', $servicePackageId)
            ->orderBy('spd.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByMedicalService(int $medicalServiceId): array
    {
        return $this->createQueryBuilder('spd')
            ->where('spd.medicalService = :medicalServiceId')
            ->setParameter('medicalServiceId', $medicalServiceId)
            ->getQuery()
            ->getResult();
    }
}
