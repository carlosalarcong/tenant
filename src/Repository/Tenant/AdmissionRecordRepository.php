<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\AdmissionRecord;
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

    public function findActiveByService(int $serviceId): array
    {
        return $this->createQueryBuilder('ar')
            ->leftJoin('ar.bed', 'bed')
            ->leftJoin('ar.person', 'person')
            ->addSelect('bed', 'person')
            ->where('ar.service = :serviceId')
            ->andWhere('LOWER(ar.status) = :status')
            ->setParameter('serviceId', $serviceId)
            ->setParameter('status', 'admitted')
            ->getQuery()
            ->getResult();
    }

    public function countPendingRequestsByService(int $serviceId): int
    {
        return (int) $this->createQueryBuilder('ar')
            ->select('COUNT(ar.id)')
            ->where('ar.service = :serviceId')
            ->andWhere('ar.bed IS NULL')
            ->andWhere('LOWER(ar.status) IN (:statuses)')
            ->setParameter('serviceId', $serviceId)
            ->setParameter('statuses', self::PENDING_STATUS_CANDIDATES)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findPendingByIdAndService(int $admissionRecordId, int $serviceId): ?AdmissionRecord
    {
        return $this->createQueryBuilder('ar')
            ->where('ar.id = :admissionRecordId')
            ->andWhere('ar.service = :serviceId')
            ->andWhere('ar.bed IS NULL')
            ->andWhere('LOWER(ar.status) IN (:statuses)')
            ->setParameter('admissionRecordId', $admissionRecordId)
            ->setParameter('serviceId', $serviceId)
            ->setParameter('statuses', self::PENDING_STATUS_CANDIDATES)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findPendingListByService(int $serviceId): array
    {
        return $this->createQueryBuilder('ar')
            ->leftJoin('ar.person', 'person')
            ->addSelect('person')
            ->where('ar.service = :serviceId')
            ->andWhere('ar.bed IS NULL')
            ->andWhere('LOWER(ar.status) IN (:statuses)')
            ->setParameter('serviceId', $serviceId)
            ->setParameter('statuses', self::PENDING_STATUS_CANDIDATES)
            ->orderBy('ar.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
