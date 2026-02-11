<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\CompanyAgreement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompanyAgreement>
 */
class CompanyAgreementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyAgreement::class);
    }

    /**
     * Encuentra todos los convenios activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('ca')
            ->where('ca.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('ca.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
