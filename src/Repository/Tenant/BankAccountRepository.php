<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BankAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BankAccount>
 */
class BankAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankAccount::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('ba')
            ->where('ba.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('ba.accountNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByBank(int $bankId): array
    {
        return $this->createQueryBuilder('ba')
            ->where('ba.bank = :bankId')
            ->setParameter('bankId', $bankId)
            ->orderBy('ba.accountNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByAccountType(int $typeId): array
    {
        return $this->createQueryBuilder('ba')
            ->where('ba.bankAccountType = :typeId')
            ->setParameter('typeId', $typeId)
            ->orderBy('ba.accountNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
