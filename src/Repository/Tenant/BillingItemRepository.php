<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BillingItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BillingItem>
 */
class BillingItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BillingItem::class);
    }

    /**
     * Encuentra todos los items activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('bi')
            ->where('bi.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('bi.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Busca ítems de facturación activos cuyo nombre coincida con la consulta.
     * Búsqueda case-insensitive con LIKE.
     *
     * Nota: la entidad BillingItem no tiene campo `code` ni `unitAmount`;
     * esos campos se omiten del resultado (null/false).
     *
     * @return BillingItem[]
     */
    public function searchByQuery(string $query, int $limit = 15): array
    {
        $q = '%' . mb_strtolower(trim($query)) . '%';

        return $this->createQueryBuilder('bi')
            ->where('bi.isActive = :active')
            ->andWhere('LOWER(bi.name) LIKE :q')
            ->setParameter('active', true)
            ->setParameter('q', $q)
            ->orderBy('bi.name', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
