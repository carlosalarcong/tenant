<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\PaymentAccount;
use App\Entity\Tenant\PaymentAccountDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaymentAccountDetail>
 */
class PaymentAccountDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentAccountDetail::class);
    }

    /**
     * Retorna los detalles activos (no anulados) de un pago, con la forma de pago cargada.
     *
     * @return PaymentAccountDetail[]
     */
    public function findByPaymentAccount(PaymentAccount $paymentAccount): array
    {
        return $this->createQueryBuilder('pad')
            ->leftJoin('pad.paymentMethod', 'pm')->addSelect('pm')
            ->where('pad.paymentAccount = :pa')
            ->andWhere('pad.isCancelled = false')
            ->setParameter('pa', $paymentAccount)
            ->orderBy('pad.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
