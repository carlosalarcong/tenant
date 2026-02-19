<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\NursingOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<NursingOrder> */
class NursingOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NursingOrder::class);
    }

    public function findByAdmissionRecord(int $admissionRecordId): array
    {
        return $this->createQueryBuilder('no')
            ->andWhere('no.admissionRecord = :admissionRecordId')
            ->setParameter('admissionRecordId', $admissionRecordId)
            ->orderBy('no.orderedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
