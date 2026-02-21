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

        // Paso 1 — búsqueda exacta: ambas condiciones comparan contra la columna sin
        // funciones, por lo que pueden aprovechar el índice de identification.
        // Se busca tanto el valor crudo (ej. "12.345.678-9") como el normalizado
        // (ej. "123456789") porque distintos tenants pueden almacenar cualquiera de
        // los dos formatos.
        /** @var array{name:string|null,last_name:string|null,middle_name:string|null}|false $row */
        $row = $connection->fetchAssociative(
            <<<'SQL'
                SELECT name, last_name, middle_name
                FROM person
                WHERE identification = :document
                   OR identification = :normalized
                ORDER BY id DESC
                LIMIT 1
            SQL,
            [
                'document'   => trim($document),
                'normalized' => $normalizedDocument,
            ]
        );

        // Paso 2 — fallback fuzzy: normaliza la columna en BD para tolerar formatos
        // mixtos (con/sin puntos o guión). Solo se ejecuta si el paso 1 no encontró
        // nada; es una búsqueda cara (no usa índice normal) pero poco frecuente.
        if ($row === false) {
            $row = $connection->fetchAssociative(
                <<<'SQL'
                    SELECT name, last_name, middle_name
                    FROM person
                    WHERE LOWER(REPLACE(REPLACE(REPLACE(identification, '.', ''), '-', ''), ' ', '')) = :normalized
                    ORDER BY id DESC
                    LIMIT 1
                SQL,
                ['normalized' => $normalizedDocument]
            );
        }

        // Ante duplicados de identification, ORDER BY id DESC garantiza que se
        // devuelve el registro de mayor id, que es el más reciente y se considera
        // la entrada canónica del padrón en este contexto clínico.
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

