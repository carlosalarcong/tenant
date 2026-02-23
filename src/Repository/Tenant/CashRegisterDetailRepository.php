<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\CashRegisterDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CashRegisterDetail>
 */
class CashRegisterDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CashRegisterDetail::class);
    }

    /**
     * Carga los detalles de cierre de una caja con PaymentMethod y Bank en una sola query.
     *
     * @return CashRegisterDetail[]
     */
    public function findByCashRegisterId(int $cashRegisterId): array
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.paymentMethod', 'pm')
            ->leftJoin('d.bank', 'b')
            ->addSelect('pm', 'b')
            ->where('d.cashRegister = :id')
            ->setParameter('id', $cashRegisterId)
            ->getQuery()
            ->getResult();
    }
}
