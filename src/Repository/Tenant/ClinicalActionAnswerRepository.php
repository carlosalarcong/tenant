<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ClinicalActionAnswer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClinicalActionAnswer>
 */
class ClinicalActionAnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClinicalActionAnswer::class);
    }

    /**
     * @return ClinicalActionAnswer[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isActive = :isActive')
            ->setParameter('isActive', true)
            ->orderBy('c.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ClinicalActionAnswer[]
     */
    public function findByQuestion(int $questionId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.question = :questionId')
            ->setParameter('questionId', $questionId)
            ->orderBy('c.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
