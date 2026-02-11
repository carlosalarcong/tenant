<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BankAccountType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BankAccountType>
 */
class BankAccountTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankAccountType::class);
    }

    /**
     * Encuentra todos los tipos de cuenta bancaria activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('bat')
            ->where('bat.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('bat.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
