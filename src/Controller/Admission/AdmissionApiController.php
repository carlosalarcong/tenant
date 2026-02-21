<?php

namespace App\Controller\Admission;

use App\Controller\AbstractTenantAwareController;
use App\Repository\Tenant\AgreementRepository;
use App\Repository\Tenant\BedRepository;
use App\Repository\Tenant\BranchRepository;
use App\Repository\Tenant\CareTypeRepository;
use App\Repository\Tenant\InsurancePlanRepository;
use App\Repository\Tenant\PayerRepository;
use App\Repository\Tenant\PersonRepository;
use App\Repository\Tenant\ProfessionalRepository;
use App\Repository\Tenant\ServiceRepository;
use App\Repository\Tenant\ServicePackageRepository;
use App\Repository\Tenant\OriginRepository;
use App\Repository\Tenant\SpecialtyRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admission', name: 'app_api_admission_')]
class AdmissionApiController extends AbstractTenantAwareController
{
    public function __construct(
        private BranchRepository $branchRepository,
        private ServiceRepository $serviceRepository,
        private PayerRepository $payerRepository,
        private AgreementRepository $agreementRepository,
        private BedRepository $bedRepository,
        private ProfessionalRepository $professionalRepository,
        private OriginRepository $originRepository,
        private SpecialtyRepository $specialtyRepository,
        private CareTypeRepository $careTypeRepository,
        private InsurancePlanRepository $insurancePlanRepository,
        private ServicePackageRepository $servicePackageRepository,
        private PersonRepository $personRepository
    ) {}

    #[Route('/branches', name: 'branches', methods: ['GET'])]
    public function branches(): JsonResponse
    {
        try {
            return $this->json($this->branchRepository->findActiveChoices());
        } catch (\Throwable) {
            return $this->json(
                ['error' => 'Error al cargar sucursales'],
                500
            );
        }
    }

    #[Route('/services', name: 'services', methods: ['GET'])]
    public function services(Request $request): JsonResponse
    {
        try {
            $branchId = $request->query->getInt('branch', 0);

            return $this->json($this->serviceRepository->findActiveChoicesByBranch($branchId > 0 ? $branchId : null));
        } catch (\Throwable) {
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

            return $this->json($this->payerRepository->findActiveChoicesByBranch($branchId > 0 ? $branchId : null));
        } catch (\Throwable) {
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

            return $this->json($this->agreementRepository->findActiveChoicesByPayer($payerId));
        } catch (\Throwable) {
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
            if ($serviceId <= 0) {
                return $this->json([]);
            }

            return $this->json($this->bedRepository->findActiveChoicesByService($serviceId));
        } catch (\Throwable) {
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

            return $this->json($this->professionalRepository->findActiveChoicesByBranch($branchId > 0 ? $branchId : null));
        } catch (\Throwable) {
            return $this->json(
                ['error' => 'Error al cargar profesionales'],
                500
            );
        }
    }

    #[Route('/origins', name: 'origins', methods: ['GET'])]
    public function origins(): JsonResponse
    {
        try {
            return $this->json($this->originRepository->findActiveChoices());
        } catch (\Throwable) {
            return $this->json(
                ['error' => 'Error al cargar orígenes'],
                500
            );
        }
    }

    #[Route('/specialties', name: 'specialties', methods: ['GET'])]
    public function specialties(): JsonResponse
    {
        try {
            return $this->json($this->specialtyRepository->findActiveChoices());
        } catch (\Throwable) {
            return $this->json(
                ['error' => 'Error al cargar especialidades'],
                500
            );
        }
    }

    #[Route('/care-types', name: 'care_types', methods: ['GET'])]
    public function careTypes(): JsonResponse
    {
        try {
            return $this->json($this->careTypeRepository->findActiveChoices());
        } catch (\Throwable) {
            return $this->json(
                ['error' => 'Error al cargar tipos de atención'],
                500
            );
        }
    }

    #[Route('/insurance-plans', name: 'insurance_plans', methods: ['GET'])]
    public function insurancePlans(): JsonResponse
    {
        try {
            return $this->json($this->insurancePlanRepository->findActiveChoices());
        } catch (\Throwable) {
            return $this->json(
                ['error' => 'Error al cargar planes previsionales'],
                500
            );
        }
    }

    #[Route('/service-packages', name: 'service_packages', methods: ['GET'])]
    public function servicePackages(): JsonResponse
    {
        try {
            return $this->json($this->servicePackageRepository->findActiveChoices());
        } catch (\Throwable) {
            return $this->json(
                ['error' => 'Error al cargar paquetes'],
                500
            );
        }
    }

    #[Route('/tutor-search', name: 'tutor_search', methods: ['GET'])]
    public function tutorSearch(Request $request): JsonResponse
    {
        $document = trim((string) $request->query->get('document', ''));
        if ($document === '') {
            return $this->json(
                ['error' => 'Parámetro "document" requerido'],
                400
            );
        }

        try {
            $name = $this->personRepository->findTutorFullNameByDocument($document);

            return $this->json([
                'name' => $name,
            ]);
        } catch (\Throwable) {
            return $this->json(
                ['error' => 'Error al buscar tutor'],
                500
            );
        }
    }
}
