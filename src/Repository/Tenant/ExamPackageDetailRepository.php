<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ExamPackageDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExamPackageDetail>
 */
class ExamPackageDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExamPackageDetail::class);
    }

    public function findByExamPackage(int $examPackageId): array
    {
        return $this->createQueryBuilder('epd')
            ->where('epd.examPackage = :examPackageId')
            ->setParameter('examPackageId', $examPackageId)
            ->orderBy('epd.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByMedicalService(int $medicalServiceId): array
    {
        return $this->createQueryBuilder('epd')
            ->where('epd.medicalService = :medicalServiceId')
            ->setParameter('medicalServiceId', $medicalServiceId)
            ->getQuery()
            ->getResult();
    }
}
