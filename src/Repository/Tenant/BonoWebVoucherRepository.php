<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BonoWebVoucher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BonoWebVoucher>
 */
class BonoWebVoucherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BonoWebVoucher::class);
    }

    /** Busca por el UUID externo de Snabb. */
    public function findByVoucherId(string $voucherId): ?BonoWebVoucher
    {
        return $this->findOneBy(['voucherId' => $voucherId]);
    }
}
