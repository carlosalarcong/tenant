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

    /**
     * Regla legacy (Paso 2 / Resguardo Financiero):
     * - activo
     * - visible en caja
     * - marcado como garantia
     * - excluir tipo de forma de pago = 3
     *
     * @return array<int, array{id:int,name:string,paymentMethodTypeId:?int}>
     */
    public function findForAdmissionFinancialSafeguard(): array
    {
        /** @var array<int, array{id:int,name:string,paymentMethodTypeId:?int}> $rows */
        $rows = $this->createQueryBuilder('pm')
            ->select('pm.id AS id', 'pm.name AS name', 'pmt.id AS paymentMethodTypeId')
            ->leftJoin('pm.paymentMethodType', 'pmt')
            ->where('pm.isActive = :active')
            ->andWhere('pm.visibleInCashRegister = :visibleInCashRegister')
            ->andWhere('pm.isGuarantee = :isGuarantee')
            ->andWhere('(pmt.id IS NULL OR pmt.id != :excludedType)')
            ->setParameter('active', true)
            ->setParameter('visibleInCashRegister', true)
            ->setParameter('isGuarantee', true)
            ->setParameter('excludedType', 3)
            ->orderBy('pm.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }
}
