<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\PhysicalExamGrouping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PhysicalExamGrouping>
 */
class PhysicalExamGroupingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PhysicalExamGrouping::class);
    }

    /**
     * Encuentra todas las agrupaciones de examen físico activas
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('peg')
            ->where('peg.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('peg.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
