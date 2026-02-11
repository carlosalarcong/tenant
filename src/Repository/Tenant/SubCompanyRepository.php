<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\SubCompany;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubCompany>
 */
class SubCompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubCompany::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('sc')
            ->where('sc.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('sc.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCode(string $code): ?SubCompany
    {
        return $this->createQueryBuilder('sc')
            ->where('sc.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByTaxId(string $taxId): ?SubCompany
    {
        return $this->createQueryBuilder('sc')
            ->where('sc.taxId = :taxId')
            ->setParameter('taxId', $taxId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
