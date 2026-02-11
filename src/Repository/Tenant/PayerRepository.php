<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Payer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payer>
 */
class PayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payer::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByType(int $typeId): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.payerType = :typeId')
            ->andWhere('p.isActive = :active')
            ->setParameter('typeId', $typeId)
            ->setParameter('active', true)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByRut(string $rut): ?Payer
    {
        return $this->createQueryBuilder('p')
            ->where('p.rut = :rut')
            ->setParameter('rut', $rut)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
