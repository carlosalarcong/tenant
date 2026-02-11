<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\CurrencyType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CurrencyType>
 */
class CurrencyTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CurrencyType::class);
    }

    /**
     * Encuentra todos los tipos de moneda activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('ct')
            ->where('ct.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('ct.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Busca el tipo de moneda CLP (Peso Chileno)
     */
    public function findClp(): ?CurrencyType
    {
        return $this->createQueryBuilder('ct')
            ->where('ct.isClp = :isClp')
            ->setParameter('isClp', true)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
