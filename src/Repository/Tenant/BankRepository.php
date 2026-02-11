<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Bank;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bank>
 */
class BankRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bank::class);
    }

    /**
     * Encuentra todos los bancos activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Busca banco por RUT
     */
    public function findByRut(string $rut): ?Bank
    {
        return $this->createQueryBuilder('b')
            ->where('b.rut = :rut')
            ->setParameter('rut', $rut)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
