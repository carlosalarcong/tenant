<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\PaymentAccount;
use App\Entity\Tenant\VoucherEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VoucherEntry>
 */
class VoucherEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VoucherEntry::class);
    }

    /**
     * Retorna el VoucherEntry activo de un pago, con el Voucher cargado.
     * Retorna null si el folio fue anulado o no existe.
     */
    public function findOneByPaymentAccount(PaymentAccount $paymentAccount): ?VoucherEntry
    {
        return $this->createQueryBuilder('ve')
            ->leftJoin('ve.voucher', 'v')->addSelect('v')
            ->where('ve.paymentAccount = :pa')
            ->andWhere('ve.isCancelled = false')
            ->setParameter('pa', $paymentAccount)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
