<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\NursingCharge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<NursingCharge> */
class NursingChargeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NursingCharge::class);
    }

    public function findByAdmissionRecord(int $admissionRecordId): array
    {
        return $this->createQueryBuilder('nc')
            ->andWhere('nc.admissionRecord = :admissionRecordId')
            ->setParameter('admissionRecordId', $admissionRecordId)
            ->orderBy('nc.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
