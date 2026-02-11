<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\RequestingCompany;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RequestingCompany>
 */
class RequestingCompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RequestingCompany::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('rc')
            ->where('rc.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('rc.businessName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findWithAgreement(): array
    {
        return $this->createQueryBuilder('rc')
            ->where('rc.hasAgreement = :hasAgreement')
            ->andWhere('rc.isActive = :active')
            ->setParameter('hasAgreement', true)
            ->setParameter('active', true)
            ->orderBy('rc.businessName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByRut(string $rut): ?RequestingCompany
    {
        return $this->createQueryBuilder('rc')
            ->where('rc.rut = :rut')
            ->setParameter('rut', $rut)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByIndustry(string $industry): array
    {
        return $this->createQueryBuilder('rc')
            ->where('rc.industry = :industry')
            ->andWhere('rc.isActive = :active')
            ->setParameter('industry', $industry)
            ->setParameter('active', true)
            ->orderBy('rc.businessName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
