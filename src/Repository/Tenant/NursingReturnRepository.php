<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\NursingReturn;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<NursingReturn> */
class NursingReturnRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NursingReturn::class);
    }

    public function findByAdmissionRecord(int $admissionRecordId): array
    {
        return $this->createQueryBuilder('nr')
            ->andWhere('nr.admissionRecord = :admissionRecordId')
            ->setParameter('admissionRecordId', $admissionRecordId)
            ->orderBy('nr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
