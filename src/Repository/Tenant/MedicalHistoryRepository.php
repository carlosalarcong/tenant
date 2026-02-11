<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\MedicalHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MedicalHistory>
 */
class MedicalHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MedicalHistory::class);
    }

    /**
     * @return MedicalHistory[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('mh')
            ->where('mh.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('mh.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
