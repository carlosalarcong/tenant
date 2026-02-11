<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\CreditCard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CreditCard>
 */
class CreditCardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CreditCard::class);
    }

    /**
     * Encuentra todas las tarjetas de crédito activas
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('cc')
            ->where('cc.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('cc.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
