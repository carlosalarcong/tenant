<?php

namespace App\Controller\Nursing;

use App\Repository\Tenant\ArticleRepository;
use App\Repository\Tenant\BedRepository;
use App\Repository\Tenant\CareInterventionRepository;
use App\Controller\AbstractTenantAwareController;
use App\Repository\Tenant\ServicePackageRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/nursing', name: 'app_nursing_api_')]
class NursingApiController extends AbstractTenantAwareController
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private CareInterventionRepository $careInterventionRepository,
        private ServicePackageRepository $servicePackageRepository,
        private BedRepository $bedRepository
    ) {}

    #[Route('/articles', name: 'articles', methods: ['GET'])]
    public function articles(Request $request): JsonResponse
    {
        $q = trim((string) $request->query->get('q', ''));
        $items = $this->articleRepository->searchActiveByTerm($q, 20);

        return $this->json(array_map(static fn($item) => [
            'id' => $item->getId(),
            'code' => $item->getCode(),
            'name' => $item->getName(),
        ], $items));
    }

    #[Route('/interventions', name: 'interventions', methods: ['GET'])]
    public function interventions(Request $request): JsonResponse
    {
        $q = trim((string) $request->query->get('q', ''));
        $items = $this->careInterventionRepository->searchActiveByTerm($q, 20);

        return $this->json(array_map(static fn($item) => [
            'id' => $item->getId(),
            'description' => $item->getDescription(),
        ], $items));
    }

    #[Route('/packages', name: 'packages', methods: ['GET'])]
    public function packages(Request $request): JsonResponse
    {
        $q = trim((string) $request->query->get('q', ''));
        $items = $this->servicePackageRepository->searchActiveByTerm($q, 20);

        return $this->json(array_map(static fn($item) => [
            'id' => $item->getId(),
            'code' => $item->getCode(),
            'name' => $item->getName(),
        ], $items));
    }

    #[Route('/packages/{id}/items', name: 'package_items', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function packageItems(int $id): JsonResponse
    {
        $package = $this->servicePackageRepository->findWithDetails($id);
        if (!$package) {
            return $this->json([]);
        }

        return $this->json(array_map(static fn($detail) => [
            'id' => $detail->getId(),
            'medicalServiceId' => $detail->getMedicalService()?->getId(),
            'medicalServiceName' => $detail->getMedicalService()?->getName(),
            'quantity' => $detail->getQuantity(),
        ], $package->getDetails()->toArray()));
    }

    #[Route('/bed-count/{serviceId}', name: 'bed_count', methods: ['GET'], requirements: ['serviceId' => '\d+'])]
    public function bedCount(int $serviceId): JsonResponse
    {
        return $this->json([
            'serviceId' => $serviceId,
            'availableBeds' => $this->bedRepository->countAvailableBedsForService($serviceId),
        ]);
    }
}
