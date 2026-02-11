<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\TransferIndicator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransferIndicator>
 */
class TransferIndicatorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransferIndicator::class);
    }

    /**
     * Encuentra todos los indicadores de traslado activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('ti')
            ->where('ti.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('ti.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Busca indicador de traslado por código
     */
    public function findByCode(int $code): ?TransferIndicator
    {
        return $this->createQueryBuilder('ti')
            ->where('ti.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
