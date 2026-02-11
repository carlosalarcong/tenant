<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\PaymentCondition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaymentCondition>
 */
class PaymentConditionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentCondition::class);
    }

    /**
     * Encuentra todas las condiciones de pago activas
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('pc')
            ->where('pc.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pc.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
