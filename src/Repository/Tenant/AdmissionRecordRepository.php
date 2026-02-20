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
            ->leftJoin('ar.patient', 'patient')
            ->leftJoin('patient.person', 'person')
            ->addSelect('bed', 'patient', 'person')
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
            ->leftJoin('ar.patient', 'patient')
            ->leftJoin('patient.person', 'person')
            ->addSelect('patient', 'person')
            ->where('ar.service = :serviceId')
            ->andWhere('ar.bed IS NULL')
            ->andWhere('LOWER(ar.status) IN (:statuses)')
            ->setParameter('serviceId', $serviceId)
            ->setParameter('statuses', self::PENDING_STATUS_CANDIDATES)
            ->orderBy('ar.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<AdmissionRecord>
     */
    public function findRecentByPersonId(int $personId): array
    {
        if ($personId <= 0) {
            return [];
        }

        /** @var list<AdmissionRecord> $records */
        $records = $this->createQueryBuilder('ar')
            ->select('ar', 'pat', 'per', 'admissionStatus')
            ->join('ar.patient', 'pat')
            ->join('pat.person', 'per')
            ->leftJoin('ar.admissionStatus', 'admissionStatus')
            ->where('per.id = :personId')
            ->setParameter('personId', $personId)
            ->orderBy('ar.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $records;
    }

    /**
     * @param list<int> $personIds
     * @return list<AdmissionRecord>
     */
    public function findRecentByPersonIds(array $personIds): array
    {
        $personIds = array_values(array_unique(array_map('intval', $personIds)));
        if ($personIds === []) {
            return [];
        }

        /** @var list<AdmissionRecord> $records */
        $records = $this->createQueryBuilder('ar')
            ->select('ar', 'pat', 'per', 'payer', 'agreement', 'admissionStatus')
            ->join('ar.patient', 'pat')
            ->join('pat.person', 'per')
            ->leftJoin('ar.payer', 'payer')
            ->leftJoin('ar.agreement', 'agreement')
            ->leftJoin('ar.admissionStatus', 'admissionStatus')
            ->where('per.id IN (:personIds)')
            ->setParameter('personIds', $personIds)
            ->orderBy('ar.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $records;
    }
}
