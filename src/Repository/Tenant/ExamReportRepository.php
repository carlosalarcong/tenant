<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ExamReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExamReport>
 */
class ExamReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExamReport::class);
    }

    /**
     * Encuentra todos los informes activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('er')
            ->where('er.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('er.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
