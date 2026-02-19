<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Person>
 */
class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    public function findTutorFullNameByDocument(string $document): ?string
    {
        $normalizedDocument = $this->normalizeDocument($document);
        if ($normalizedDocument === '') {
            return null;
        }

        $connection = $this->getEntityManager()->getConnection();

        /** @var array{name:string|null,last_name:string|null,middle_name:string|null}|false $row */
        $row = $connection->fetchAssociative(
            <<<'SQL'
                SELECT name, last_name, middle_name
                FROM person
                WHERE identification = :document
                   OR LOWER(REPLACE(REPLACE(REPLACE(identification, '.', ''), '-', ''), ' ', '')) = :normalized
                ORDER BY id DESC
                LIMIT 1
            SQL,
            [
                'document' => trim($document),
                'normalized' => $normalizedDocument,
            ]
        );

        if ($row === false) {
            return null;
        }

        $fullName = trim(implode(' ', array_filter([
            $row['name'] ?? null,
            $row['last_name'] ?? null,
            $row['middle_name'] ?? null,
        ], static fn($value) => is_string($value) && trim($value) !== '')));

        return $fullName !== '' ? $fullName : null;
    }

    private function normalizeDocument(string $value): string
    {
        return strtolower(str_replace(['.', '-', ' '], '', trim($value)));
    }
}

