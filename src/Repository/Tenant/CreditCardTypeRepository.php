<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\CreditCardType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CreditCardType>
 */
class CreditCardTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreditCardType::class);
    }

    /**
     * Encuentra todos los tipos de tarjeta de crédito activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('cct')
            ->where('cct.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('cct.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
