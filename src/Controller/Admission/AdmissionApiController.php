<?php

namespace App\Controller\Admission;

use App\Controller\AbstractTenantAwareController;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admission', name: 'app_api_admission_')]
class AdmissionApiController extends AbstractTenantAwareController
{
    public function __construct(
        private TenantEntityManager $entityManager
    ) {}

    #[Route('/services', name: 'services', methods: ['GET'])]
    public function services(Request $request): JsonResponse
    {
        try {
            $branchId = $request->query->getInt('branch', 0);
            $connection = $this->entityManager->getConnection();
            
            // Fallback: no hay relación branch-service en esquema actual, traer todos activos
            $sql = 'SELECT id, name FROM service WHERE is_active = true ORDER BY name ASC LIMIT 300';
            $params = [];
            
            if ($branchId > 0) {
                // TODO: agregar filtro por sucursal cuando exista relación service-branch
                // $sql = 'SELECT s.id, s.name FROM service s ...';
                // $params = ['branch' => $branchId];
            }
            
            $rows = $connection->executeQuery($sql, $params)->fetchAllAssociative();
            
            return $this->json($rows);
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'Error al cargar servicios'],
                500
            );
        }
    }

    #[Route('/payers', name: 'payers', methods: ['GET'])]
    public function payers(Request $request): JsonResponse
    {
        try {
            $branchId = $request->query->getInt('branch', 0);
            $connection = $this->entityManager->getConnection();
            
            // Fallback: no hay relación branch-payer en esquema actual, traer todos activos
            $sql = 'SELECT id, name FROM payer WHERE is_active = true ORDER BY name ASC LIMIT 300';
            $params = [];
            
            if ($branchId > 0) {
                // TODO: agregar filtro por sucursal cuando exista relación payer-branch
                // $sql = 'SELECT p.id, p.name FROM payer p ...';
                // $params = ['branch' => $branchId];
            }
            
            $rows = $connection->executeQuery($sql, $params)->fetchAllAssociative();
            
            return $this->json($rows);
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'Error al cargar financiadores'],
                500
            );
        }
    }

    #[Route('/agreements', name: 'agreements', methods: ['GET'])]
    public function agreements(Request $request): JsonResponse
    {
        try {
            $payerId = $request->query->getInt('payer', 0);
            
            if ($payerId <= 0) {
                return $this->json(
                    ['error' => 'Parámetro "payer" requerido y debe ser > 0'],
                    400
                );
            }
            
            $connection = $this->entityManager->getConnection();
            $rows = $connection->executeQuery(
                'SELECT id, name FROM agreement WHERE is_active = true AND payer_id = :payer ORDER BY name ASC LIMIT 300',
                ['payer' => $payerId]
            )->fetchAllAssociative();
            
            return $this->json($rows);
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'Error al cargar convenios'],
                500
            );
        }
    }

    #[Route('/beds', name: 'beds', methods: ['GET'])]
    public function beds(Request $request): JsonResponse
    {
        try {
            $serviceId = $request->query->getInt('service', 0);
            $connection = $this->entityManager->getConnection();
            
            $sql = "SELECT id, CONCAT('Cama ', bed_number, ' (Piso ', COALESCE(CAST(floor AS TEXT), '-'), ')') AS name
                    FROM bed
                    WHERE is_active = true";
            $params = [];
            
            if ($serviceId > 0) {
                // TODO: agregar filtro por servicio cuando exista relación bed-service
                // $sql .= ' AND service_id = :service';
                // $params['service'] = $serviceId;
            }
            
            $sql .= ' ORDER BY bed_number ASC LIMIT 300';
            
            $rows = $connection->executeQuery($sql, $params)->fetchAllAssociative();
            
            return $this->json($rows);
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'Error al cargar camas'],
                500
            );
        }
    }

    #[Route('/professionals', name: 'professionals', methods: ['GET'])]
    public function professionals(Request $request): JsonResponse
    {
        try {
            $branchId = $request->query->getInt('branch', 0);
            $connection = $this->entityManager->getConnection();
            
            $sql = "SELECT id, CONCAT(first_name, ' ', last_name) AS name
                    FROM professional
                    WHERE is_active = true";
            $params = [];
            
            if ($branchId > 0) {
                // TODO: agregar filtro por sucursal cuando exista relación professional-branch
                // $sql .= ' AND branch_id = :branch';
                // $params['branch'] = $branchId;
            }
            
            $sql .= ' ORDER BY first_name ASC, last_name ASC LIMIT 300';
            
            $rows = $connection->executeQuery($sql, $params)->fetchAllAssociative();
            
            return $this->json($rows);
        } catch (\Exception $e) {
            return $this->json(
                ['error' => 'Error al cargar profesionales'],
                500
            );
        }
    }
}

