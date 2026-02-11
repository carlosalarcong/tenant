<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\PaymentMethod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaymentMethod>
 */
class PaymentMethodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentMethod::class);
    }

    /**
     * Encuentra todos los métodos de pago activos
     *
     * @return PaymentMethod[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('pm')
            ->where('pm.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pm.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra métodos de pago padres (sin padre)
     *
     * @return PaymentMethod[]
     */
    public function findParentMethods(): array
    {
        return $this->createQueryBuilder('pm')
            ->where('pm.parent IS NULL')
            ->andWhere('pm.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pm.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
