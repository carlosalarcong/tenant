<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\PayerType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PayerType>
 */
class PayerTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PayerType::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('pt')
            ->where('pt.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCode(string $code): ?PayerType
    {
        return $this->createQueryBuilder('pt')
            ->where('pt.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
