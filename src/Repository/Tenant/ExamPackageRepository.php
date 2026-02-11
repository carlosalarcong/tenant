<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ExamPackage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExamPackage>
 */
class ExamPackageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExamPackage::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('ep')
            ->where('ep.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('ep.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCode(string $code): ?ExamPackage
    {
        return $this->createQueryBuilder('ep')
            ->where('ep.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
