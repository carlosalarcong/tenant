<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\NursingDischarge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<NursingDischarge> */
class NursingDischargeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NursingDischarge::class);
    }

    public function findRecentByService(int $serviceId): array
    {
        return $this->createQueryBuilder('nd')
            ->innerJoin('nd.admissionRecord', 'ar')
            ->andWhere('ar.service = :serviceId')
            ->setParameter('serviceId', $serviceId)
            ->orderBy('nd.dischargedAt', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    public function countByServiceAndType(int $serviceId, ?string $dischargeType = null): int
    {
        $qb = $this->createQueryBuilder('nd')
            ->select('COUNT(nd.id)')
            ->innerJoin('nd.admissionRecord', 'ar')
            ->andWhere('ar.service = :serviceId')
            ->setParameter('serviceId', $serviceId);

        if (null !== $dischargeType && '' !== trim($dischargeType)) {
            $qb
                ->andWhere('nd.dischargeType = :dischargeType')
                ->setParameter('dischargeType', $dischargeType);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
