<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\NursingTransfer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<NursingTransfer> */
class NursingTransferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NursingTransfer::class);
    }

    public function findPendingByDestinationService(int $serviceId): array
    {
        return $this->createQueryBuilder('nt')
            ->innerJoin('nt.admissionRecord', 'ar')
            ->leftJoin('ar.person', 'person')
            ->leftJoin('person.identificationType', 'identificationType')
            ->addSelect('ar', 'person', 'identificationType')
            ->andWhere('nt.destinationService = :serviceId')
            ->andWhere('nt.status = :status')
            ->setParameter('serviceId', $serviceId)
            ->setParameter('status', 'pending')
            ->orderBy('nt.requestedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countPendingByDestinationService(int $serviceId): int
    {
        return (int) $this->createQueryBuilder('nt')
            ->select('COUNT(nt.id)')
            ->andWhere('nt.destinationService = :serviceId')
            ->andWhere('nt.status = :status')
            ->setParameter('serviceId', $serviceId)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countPendingByOriginService(int $serviceId): int
    {
        return (int) $this->createQueryBuilder('nt')
            ->select('COUNT(nt.id)')
            ->andWhere('nt.originService = :serviceId')
            ->andWhere('nt.status = :status')
            ->setParameter('serviceId', $serviceId)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
