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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdmissionRecord::class);
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
