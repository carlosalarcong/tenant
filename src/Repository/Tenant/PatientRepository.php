<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Patient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Patient>
 */
class PatientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Patient::class);
    }

    public function findByPersonId(int $personId): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.person', 'per')
            ->where('per.id = :personId')
            ->setParameter('personId', $personId)
            ->orderBy('p.admissionDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Búsqueda avanzada por nombre, apellido paterno y/o materno con modo configurable.
     *
     * @param string      $firstName    Nombre (vacío = ignorado)
     * @param string      $lastName     Apellido paterno (vacío = ignorado)
     * @param string      $motherName   Apellido materno (vacío = ignorado)
     * @param string      $matchType    'contains' | 'starts' | 'exact'
     * @return Patient[]
     */
    public function searchByAdvanced(
        string $firstName,
        string $lastName,
        string $motherName,
        string $matchType = 'exact',
        int    $limit = 10
    ): array {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.person', 'per')
            ->addSelect('per')
            ->leftJoin('p.payer', 'pay')
            ->addSelect('pay')
            ->leftJoin('p.agreement', 'agr')
            ->addSelect('agr')
            ->leftJoin('p.insurancePlan', 'plan')
            ->addSelect('plan');

        $conditions = [];

        foreach ([
            'firstName'  => ['per.name',     $firstName],
            'lastName'   => ['per.lastName',  $lastName],
            'motherName' => ['per.motherName', $motherName],
        ] as $param => [$field, $value]) {
            if (trim($value) === '') {
                continue;
            }
            $v = mb_strtolower(trim($value));
            $placeholder = ':' . $param;
            $conditions[] = "LOWER({$field}) LIKE {$placeholder}";
            $qb->setParameter($param, match ($matchType) {
                'contains' => '%' . $v . '%',
                'starts'   => $v . '%',
                default    => $v,         // exact
            });
        }

        if (empty($conditions)) {
            return [];
        }

        $qb->where(implode(' AND ', $conditions))
            ->orderBy('p.admissionDate', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Busca pacientes por RUT (identification) o por nombre/apellido.
     * Búsqueda case-insensitive con LIKE.
     *
     * @return Patient[]
     */
    public function searchByQuery(string $query, int $limit = 10): array
    {
        $rawQuery = trim($query);
        $q = '%' . mb_strtolower($rawQuery) . '%';
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.person', 'per')
            ->addSelect('per')
            ->leftJoin('p.payer', 'pay')
            ->addSelect('pay')
            ->leftJoin('p.agreement', 'agr')
            ->addSelect('agr')
            ->leftJoin('p.insurancePlan', 'plan')
            ->addSelect('plan')
            ->where('LOWER(per.identification) LIKE :q')
            ->orWhere('LOWER(per.name) LIKE :q')
            ->orWhere('LOWER(per.lastName) LIKE :q')
            ->setParameter('q', $q)
            ->orderBy('p.admissionDate', 'DESC')
            ->setMaxResults($limit);

        foreach ($this->buildRutSearchVariants($rawQuery) as $index => $variant) {
            $param = 'rutv' . $index;
            $qb->orWhere('LOWER(per.identification) LIKE :' . $param)
               ->setParameter($param, '%' . mb_strtolower($variant) . '%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Genera variantes comunes del RUT para búsqueda flexible sin funciones SQL.
     * Ejemplo base: 151261361 -> 15.126.136-1, 15126136-1, 15.126.1361
     *
     * @return string[]
     */
    private function buildRutSearchVariants(string $query): array
    {
        $normalized = (string) preg_replace('/[^0-9kK]/', '', $query);
        $normalized = mb_strtolower($normalized);

        if (!preg_match('/^[0-9]{7,8}[0-9k]$/', $normalized)) {
            return [];
        }

        $body = substr($normalized, 0, -1);
        $dv = substr($normalized, -1);

        $formattedBody = $this->formatRutBodyWithDots($body);

        return array_values(array_unique([
            $normalized,                 // 151261361
            $body . '-' . $dv,           // 15126136-1
            $formattedBody . '-' . $dv,  // 15.126.136-1
            $formattedBody . $dv,        // 15.126.1361
        ]));
    }

    private function formatRutBodyWithDots(string $body): string
    {
        $result = '';
        $counter = 0;

        for ($i = strlen($body) - 1; $i >= 0; $i--) {
            $result = $body[$i] . $result;
            $counter++;

            if ($counter % 3 === 0 && $i !== 0) {
                $result = '.' . $result;
            }
        }

        return $result;
    }
}
