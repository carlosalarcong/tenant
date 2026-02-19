<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\NursingPrescription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<NursingPrescription> */
class NursingPrescriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NursingPrescription::class);
    }

    public function findByAdmissionRecord(int $admissionRecordId): array
    {
        return $this->createQueryBuilder('np')
            ->andWhere('np.admissionRecord = :admissionRecordId')
            ->setParameter('admissionRecordId', $admissionRecordId)
            ->orderBy('np.prescribedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
