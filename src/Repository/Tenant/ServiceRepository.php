<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BranchServiceType;
use App\Entity\Tenant\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Service>
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByServiceType(int $serviceTypeId): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.serviceType = :serviceTypeId')
            ->andWhere('s.isActive = :active')
            ->setParameter('serviceTypeId', $serviceTypeId)
            ->setParameter('active', true)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySpecialty(int $specialtyId): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.specialty = :specialtyId')
            ->andWhere('s.isActive = :active')
            ->setParameter('specialtyId', $specialtyId)
            ->setParameter('active', true)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCode(string $code): ?Service
    {
        return $this->createQueryBuilder('s')
            ->where('s.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAvailableServices(): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.isAvailable = :available')
            ->andWhere('s.isActive = :active')
            ->setParameter('available', true)
            ->setParameter('active', true)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneActiveById(int $id): ?Service
    {
        return $this->createQueryBuilder('s')
            ->where('s.id = :id')
            ->andWhere('s.isActive = :active')
            ->setParameter('id', $id)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    public function findActiveChoicesByBranch(?int $branchId = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('DISTINCT s.id AS id', 's.name AS name')
            ->where('s.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('s.name', 'ASC');

        if ($branchId !== null && $branchId > 0) {
            $qb->join(BranchServiceType::class, 'bst', 'WITH', 'bst.serviceType = s.serviceType')
                ->andWhere('IDENTITY(bst.branch) = :branchId')
                ->andWhere('bst.isActive = :branchServiceTypeActive')
                ->setParameter('branchId', $branchId)
                ->setParameter('branchServiceTypeActive', true);
        }

        /** @var array<int, array{id:int,name:string}> $rows */
        $rows = $qb->getQuery()->getArrayResult();

        return $rows;
    }

}
