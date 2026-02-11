<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\MedicalServiceService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MedicalServiceService>
 */
class MedicalServiceServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MedicalServiceService::class);
    }

    public function findByMedicalService(int $medicalServiceId): array
    {
        return $this->createQueryBuilder('mss')
            ->where('mss.medicalService = :medicalServiceId')
            ->setParameter('medicalServiceId', $medicalServiceId)
            ->getQuery()
            ->getResult();
    }

    public function findByService(int $serviceId): array
    {
        return $this->createQueryBuilder('mss')
            ->where('mss.service = :serviceId')
            ->setParameter('serviceId', $serviceId)
            ->getQuery()
            ->getResult();
    }
}
