<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\SurgeryItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SurgeryItem>
 */
class SurgeryItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurgeryItem::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('si')
            ->where('si.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('si.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('si')
            ->where('si.category = :category')
            ->andWhere('si.isActive = :active')
            ->setParameter('category', $category)
            ->setParameter('active', true)
            ->orderBy('si.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findSterileItems(): array
    {
        return $this->createQueryBuilder('si')
            ->where('si.isSterile = :sterile')
            ->andWhere('si.isActive = :active')
            ->setParameter('sterile', true)
            ->setParameter('active', true)
            ->orderBy('si.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findDisposableItems(): array
    {
        return $this->createQueryBuilder('si')
            ->where('si.isDisposable = :disposable')
            ->andWhere('si.isActive = :active')
            ->setParameter('disposable', true)
            ->setParameter('active', true)
            ->orderBy('si.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCode(string $code): ?SurgeryItem
    {
        return $this->createQueryBuilder('si')
            ->where('si.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
