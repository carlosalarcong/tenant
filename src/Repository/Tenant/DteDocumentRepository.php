<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\DteDocument;
use App\Entity\Tenant\PaymentAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DteDocument>
 */
class DteDocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DteDocument::class);
    }

    /**
     * @return DteDocument[]
     */
    public function findByPaymentAccount(PaymentAccount $paymentAccount): array
    {
        return $this->findBy(['paymentAccount' => $paymentAccount]);
    }

    /**
     * @return DteDocument[]
     */
    public function findRetryPending(): array
    {
        return $this->findBy(['status' => 'retry_pending']);
    }
}
