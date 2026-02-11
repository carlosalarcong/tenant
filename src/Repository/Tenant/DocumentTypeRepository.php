<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\DocumentType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DocumentType>
 */
class DocumentTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocumentType::class);
    }

    /**
     * Encuentra todos los tipos de documento activos
     *
     * @return DocumentType[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('dt')
            ->where('dt.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('dt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
