<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\SurgeryBlockReason;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SurgeryBlockReason>
 */
class SurgeryBlockReasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurgeryBlockReason::class);
    }

    /**
     * @return SurgeryBlockReason[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('sbr')
            ->where('sbr.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('sbr.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
