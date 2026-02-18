<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\AdmissionStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdmissionStatus>
 */
class AdmissionStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdmissionStatus::class);
    }

    /**
     * @return list<AdmissionStatus>
     */
    public function findAllForResolution(): array
    {
        /** @var list<AdmissionStatus> $statuses */
        $statuses = $this->createQueryBuilder('s')
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $statuses;
    }
}
