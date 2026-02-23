<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\PaymentAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaymentAccount>
 */
class PaymentAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentAccount::class);
    }

    /**
     * Carga un PaymentAccount con sus relaciones principales para la vista de resumen.
     */
    public function findWithDetailsById(int $id): ?PaymentAccount
    {
        return $this->createQueryBuilder('pa')
            ->leftJoin('pa.patient',              'pat') ->addSelect('pat')
            ->leftJoin('pat.person',              'per') ->addSelect('per')
            ->leftJoin('pat.payer',               'pay') ->addSelect('pay')
            ->leftJoin('pa.paymentStatus',        'ps')  ->addSelect('ps')
            ->leftJoin('pa.cashRegisterLocation', 'crl') ->addSelect('crl')
            ->leftJoin('pa.createdByMember',      'mbr') ->addSelect('mbr')
            ->leftJoin('pa.cancelledByMember',    'cmbr')->addSelect('cmbr')
            ->where('pa.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Historial de pagos de un paciente, ordenado por fecha descendente.
     *
     * @return PaymentAccount[]
     */
    public function findHistoryByPatientId(int $patientId, int $limit = 20): array
    {
        return $this->createQueryBuilder('pa')
            ->leftJoin('pa.paymentStatus',        'ps')  ->addSelect('ps')
            ->leftJoin('pa.cashRegisterLocation', 'crl') ->addSelect('crl')
            ->where('pa.patient = :patientId')
            ->setParameter('patientId', $patientId)
            ->orderBy('pa.paymentDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
