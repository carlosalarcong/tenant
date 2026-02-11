<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BillingPaymentMethod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BillingPaymentMethod>
 */
class BillingPaymentMethodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BillingPaymentMethod::class);
    }

    /**
     * Encuentra todas las formas de pago de facturación activas
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('bpm')
            ->where('bpm.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('bpm.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Busca forma de pago por código
     */
    public function findByCode(string $code): ?BillingPaymentMethod
    {
        return $this->createQueryBuilder('bpm')
            ->where('bpm.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
