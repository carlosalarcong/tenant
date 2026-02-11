<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\PaymentMethodType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaymentMethodType>
 */
class PaymentMethodTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentMethodType::class);
    }

    /**
     * Encuentra todos los tipos de método de pago activos
     *
     * @return PaymentMethodType[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('pmt')
            ->where('pmt.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pmt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
