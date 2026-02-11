<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\MedicalServiceBedType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MedicalServiceBedType>
 */
class MedicalServiceBedTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MedicalServiceBedType::class);
    }

    public function findByMedicalService(int $medicalServiceId): array
    {
        return $this->createQueryBuilder('msbt')
            ->where('msbt.medicalService = :medicalServiceId')
            ->setParameter('medicalServiceId', $medicalServiceId)
            ->getQuery()
            ->getResult();
    }

    public function findByBedType(int $bedTypeId): array
    {
        return $this->createQueryBuilder('msbt')
            ->where('msbt.bedType = :bedTypeId')
            ->setParameter('bedTypeId', $bedTypeId)
            ->getQuery()
            ->getResult();
    }
}
