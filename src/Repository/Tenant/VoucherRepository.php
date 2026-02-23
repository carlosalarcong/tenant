<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\CashRegisterLocation;
use App\Entity\Tenant\Voucher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Voucher>
 */
class VoucherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Voucher::class);
    }

    /**
     * Obtiene el talonario activo con folios disponibles para la ubicación dada.
     * Usa PESSIMISTIC_WRITE para evitar consumo concurrente del mismo folio.
     *
     * DEBE llamarse dentro de una transacción activa.
     */
    public function findActiveByLocation(CashRegisterLocation $location): ?Voucher
    {
        $query = $this->createQueryBuilder('v')
            ->where('v.cashRegisterLocation = :location')
            ->andWhere('v.isActive = :active')
            ->andWhere('v.currentFolio <= v.folioTo')
            ->setParameter('location', $location)
            ->setParameter('active', true)
            ->setMaxResults(1)
            ->getQuery();

        $query->setLockMode(\Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);

        return $query->getOneOrNullResult();
    }
}
