<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\GESPathology;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GESPathology>
 */
class GESPathologyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GESPathology::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('gp')
            ->where('gp.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('gp.pathologyNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByPathologyNumber(string $number): ?GESPathology
    {
        return $this->createQueryBuilder('gp')
            ->where('gp.pathologyNumber = :number')
            ->setParameter('number', $number)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByAgeAndGender(int $age, ?string $gender): array
    {
        $qb = $this->createQueryBuilder('gp')
            ->where('gp.isActive = :active')
            ->setParameter('active', true);

        // Age filtering
        $qb->andWhere('(gp.minAge IS NULL OR gp.minAge <= :age)')
            ->andWhere('(gp.maxAge IS NULL OR gp.maxAge >= :age)')
            ->setParameter('age', $age);

        // Gender filtering
        if ($gender) {
            $qb->andWhere('(gp.genderRestriction IS NULL OR gp.genderRestriction = :gender)')
                ->setParameter('gender', $gender);
        }

        return $qb->orderBy('gp.pathologyNumber', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
