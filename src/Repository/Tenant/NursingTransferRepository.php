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
            ->andWhere('nt.destinationService = :serviceId')
            ->andWhere('nt.status = :status')
            ->setParameter('serviceId', $serviceId)
            ->setParameter('status', 'pending')
            ->orderBy('nt.requestedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
