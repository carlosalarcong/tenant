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
    private const ACTIVE_STATUS_CANDIDATES = [
        'admitido',
        'admitted',
        'hospitalizado',
    ];

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
            ->leftJoin('ar.patient', 'patient')
            ->leftJoin('patient.person', 'person')
            ->leftJoin('ar.admissionStatus', 'admissionStatus')
            ->addSelect('bed', 'patient', 'person', 'admissionStatus')
            ->where('LOWER(admissionStatus.name) IN (:activeStatuses)')
            ->setParameter('medicalServiceId', $medicalServiceId)
            ->setParameter('active', true)
            ->setParameter('activeStatuses', self::ACTIVE_STATUS_CANDIDATES)
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
            ->leftJoin('ar.admissionStatus', 'admissionStatus')
            ->andWhere('(admissionStatus.id IS NULL OR LOWER(admissionStatus.name) IN (:statuses))')
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
            ->leftJoin('ar.admissionStatus', 'admissionStatus')
            ->andWhere('(admissionStatus.id IS NULL OR LOWER(admissionStatus.name) IN (:statuses))')
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
            ->leftJoin('ar.patient', 'patient')
            ->leftJoin('patient.person', 'person')
            ->leftJoin('ar.admissionStatus', 'admissionStatus')
            ->addSelect('patient', 'person')
            ->andWhere('ar.bed IS NULL')
            ->andWhere('(admissionStatus.id IS NULL OR LOWER(admissionStatus.name) IN (:statuses))')
            ->setParameter('medicalServiceId', $medicalServiceId)
            ->setParameter('active', true)
            ->setParameter('statuses', self::PENDING_STATUS_CANDIDATES)
            ->orderBy('ar.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveByService(int $serviceId): array
    {
        return $this->createQueryBuilder('ar')
            ->leftJoin('ar.bed', 'bed')
            ->leftJoin('ar.patient', 'patient')
            ->leftJoin('patient.person', 'person')
            ->leftJoin('ar.admissionStatus', 'admissionStatus')
            ->addSelect('bed', 'patient', 'person', 'admissionStatus')
            ->where('ar.service = :serviceId')
            ->andWhere('LOWER(admissionStatus.name) IN (:activeStatuses)')
            ->setParameter('serviceId', $serviceId)
            ->setParameter('activeStatuses', self::ACTIVE_STATUS_CANDIDATES)
            ->getQuery()
            ->getResult();
    }

    public function countPendingRequestsByService(int $serviceId): int
    {
        return (int) $this->createQueryBuilder('ar')
            ->select('COUNT(ar.id)')
            ->where('ar.service = :serviceId')
            ->andWhere('ar.bed IS NULL')
            ->leftJoin('ar.admissionStatus', 'admissionStatus')
            ->andWhere('(admissionStatus.id IS NULL OR LOWER(admissionStatus.name) IN (:statuses))')
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
            ->leftJoin('ar.admissionStatus', 'admissionStatus')
            ->andWhere('(admissionStatus.id IS NULL OR LOWER(admissionStatus.name) IN (:statuses))')
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
            ->leftJoin('ar.admissionStatus', 'admissionStatus')
            ->addSelect('patient', 'person')
            ->where('ar.service = :serviceId')
            ->andWhere('ar.bed IS NULL')
            ->andWhere('(admissionStatus.id IS NULL OR LOWER(admissionStatus.name) IN (:statuses))')
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
     * Find an AdmissionRecord by ID with its PatientAccount, PaymentAccounts and
     * related statuses eagerly loaded. Used by the financial-guarantee endpoint.
     */
    public function findWithAccountById(int $admissionRecordId): ?AdmissionRecord
    {
        return $this->createQueryBuilder('ar')
            ->leftJoin('ar.patient', 'p')
            ->leftJoin('p.person', 'per')
            ->leftJoin('ar.admissionStatus', 'ads')
            ->leftJoin('ar.patientAccount', 'pa')
            ->leftJoin('pa.accountStatus', 'past')
            ->leftJoin('pa.paymentAccounts', 'paa')
            ->leftJoin('paa.paymentStatus', 'ps')
            ->addSelect('p', 'per', 'ads', 'pa', 'past', 'paa', 'ps')
            ->where('ar.id = :id')
            ->setParameter('id', $admissionRecordId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find AdmissionRecords for a Person for the pending-payment check.
     * Returns admissions where the person is the patient OR the legal guardian,
     * excluding pre-admission records (those statuses are not real debts).
     * PatientAccount and its AccountStatus are eagerly loaded.
     *
     * @return list<AdmissionRecord>
     */
    public function findForPaymentCheckByPersonId(int $personId): array
    {
        /** @var list<AdmissionRecord> $results */
        $results = $this->createQueryBuilder('ar')
            ->leftJoin('ar.patient', 'p')
            ->leftJoin('p.person', 'per')
            ->leftJoin('p.tutor', 'tut')
            ->leftJoin('ar.admissionStatus', 'ads')
            ->leftJoin('ar.patientAccount', 'pa')
            ->leftJoin('pa.accountStatus', 'past')
            ->addSelect('p', 'per', 'tut', 'ads', 'pa', 'past')
            ->where('per.id = :personId OR tut.id = :personId')
            ->andWhere('ads IS NULL OR LOWER(ads.name) NOT IN (:preadmissions)')
            ->setParameter('personId', $personId)
            ->setParameter('preadmissions', self::PENDING_STATUS_CANDIDATES)
            ->getQuery()
            ->getResult();

        return $results;
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
