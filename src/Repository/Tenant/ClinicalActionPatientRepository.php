<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ClinicalActionPatient;
use App\Entity\Tenant\PaymentAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClinicalActionPatient>
 */
class ClinicalActionPatientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClinicalActionPatient::class);
    }

    /**
     * Retorna las prestaciones activas de un pago, con el ítem de facturación cargado.
     *
     * @return ClinicalActionPatient[]
     */
    public function findByPaymentAccount(PaymentAccount $paymentAccount): array
    {
        return $this->createQueryBuilder('cap')
            ->leftJoin('cap.billingItem', 'bi')->addSelect('bi')
            ->where('cap.paymentAccount = :pa')
            ->andWhere('cap.isCancelled = false')
            ->setParameter('pa', $paymentAccount)
            ->orderBy('cap.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
