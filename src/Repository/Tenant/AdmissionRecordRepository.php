<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\AdmissionRecord;
use App\Entity\Tenant\MedicalServiceService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdmissionRecord>
 */
class AdmissionRecordRepository extends ServiceEntityRepository
{
    private const PENDING_STATUS_CANDIDATES = [
        'draft',
        'pending',
        'preadmision',
        'pre-admision',
        'pre admision',
        'preadmisión',
        'pre-admisión',
        'pre admisión',
    ];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdmissionRecord::class);
    }

    public function findActiveByMedicalService(int $medicalServiceId): array
    {
        return $this->createQueryBuilder('ar')
            ->innerJoin(
                MedicalServiceService::class,
                'mss',
                'WITH',
                'mss.service = ar.service AND mss.medicalService = :medicalServiceId AND mss.isActive = :active'
            )
            ->leftJoin('ar.bed', 'bed')
            ->leftJoin('ar.person', 'person')
            ->addSelect('bed', 'person')
            ->where('LOWER(ar.status) = :status')
            ->setParameter('medicalServiceId', $medicalServiceId)
            ->setParameter('active', true)
            ->setParameter('status', 'admitted')
            ->getQuery()
            ->getResult();
    }

    public function countPendingRequestsByMedicalService(int $medicalServiceId): int
    {
        return (int) $this->createQueryBuilder('ar')
            ->select('COUNT(ar.id)')
            ->innerJoin(
                MedicalServiceService::class,
                'mss',
                'WITH',
                'mss.service = ar.service AND mss.medicalService = :medicalServiceId AND mss.isActive = :active'
            )
            ->andWhere('ar.bed IS NULL')
            ->andWhere('LOWER(ar.status) IN (:statuses)')
            ->setParameter('medicalServiceId', $medicalServiceId)
            ->setParameter('active', true)
            ->setParameter('statuses', self::PENDING_STATUS_CANDIDATES)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findPendingByIdAndMedicalService(int $admissionRecordId, int $medicalServiceId): ?AdmissionRecord
    {
        return $this->createQueryBuilder('ar')
            ->innerJoin(
                MedicalServiceService::class,
                'mss',
                'WITH',
                'mss.service = ar.service AND mss.medicalService = :medicalServiceId AND mss.isActive = :active'
            )
            ->andWhere('ar.id = :admissionRecordId')
            ->andWhere('ar.bed IS NULL')
            ->andWhere('LOWER(ar.status) IN (:statuses)')
            ->setParameter('medicalServiceId', $medicalServiceId)
            ->setParameter('admissionRecordId', $admissionRecordId)
            ->setParameter('active', true)
            ->setParameter('statuses', self::PENDING_STATUS_CANDIDATES)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPendingListByMedicalService(int $medicalServiceId): array
    {
        return $this->createQueryBuilder('ar')
            ->innerJoin(
                MedicalServiceService::class,
                'mss',
                'WITH',
                'mss.service = ar.service AND mss.medicalService = :medicalServiceId AND mss.isActive = :active'
            )
            ->leftJoin('ar.person', 'person')
            ->addSelect('person')
            ->andWhere('ar.bed IS NULL')
            ->andWhere('LOWER(ar.status) IN (:statuses)')
            ->setParameter('medicalServiceId', $medicalServiceId)
            ->setParameter('active', true)
            ->setParameter('statuses', self::PENDING_STATUS_CANDIDATES)
            ->orderBy('ar.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
