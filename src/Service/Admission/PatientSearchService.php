<?php

namespace App\Service\Admission;

use App\Entity\Tenant\IdentificationType;
use App\Entity\Tenant\Person;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;

class PatientSearchService
{
    public function __construct(
        private TenantEntityManager $entityManager
    ) {}

    /**
     * @return IdentificationType[]
     */
    public function getActiveIdentificationTypes(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('it')
            ->from(IdentificationType::class, 'it')
            ->where('it.isActive = true')
            ->orderBy('it.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Person[]
     */
    public function searchPatients(string $searchTerm, int $selectedTypeId = 0, int $limit = 20): array
    {
        $searchTerm = trim($searchTerm);
        if ($searchTerm == '') {
            return [];
        }

        $qb = $this->entityManager->createQueryBuilder()
            ->select('p', 'it')
            ->from(Person::class, 'p')
            ->leftJoin('p.identificationType', 'it')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($limit);

        if ($selectedTypeId > 0) {
            $qb->andWhere('it.id = :typeId')
                ->setParameter('typeId', $selectedTypeId)
                ->andWhere('LOWER(p.identification) LIKE :term')
                ->setParameter('term', '%' . mb_strtolower($searchTerm) . '%');
        } else {
            $qb->andWhere(
                'LOWER(p.identification) LIKE :term OR
                 LOWER(p.name) LIKE :term OR
                 LOWER(p.lastName) LIKE :term OR
                 LOWER(CONCAT(p.name, \' \', p.lastName, \' \', COALESCE(p.middleName, \'\'))) LIKE :term'
            )
            ->setParameter('term', '%' . mb_strtolower($searchTerm) . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
