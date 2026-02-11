<?php

namespace App\Controller\Admission;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\AdmissionRecord;
use App\Form\Admission\PatientSearchType;
use App\Service\Admission\AdmissionService;
use App\Service\Admission\PatientSearchService;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admission', name: 'app_admission_')]
class AdmissionController extends AbstractTenantAwareController
{
    public function __construct(
        private TenantEntityManager $entityManager,
        private PatientSearchService $patientSearchService,
        private AdmissionService $admissionService
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    #[Route('/hospitalization', name: 'hospitalization_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->render('admission/index.html.twig', $this->buildSearchViewData(
            request: $request,
            pageTitle: 'Admisión Hospitalaria',
            searchActionRoute: 'app_admission_hospitalization_index'
        ));
    }

    #[Route('/pre', name: 'pre_index', methods: ['GET'])]
    public function preIndex(Request $request): Response
    {
        return $this->render('admission/index.html.twig', $this->buildSearchViewData(
            request: $request,
            pageTitle: 'Pre-Admisión',
            searchActionRoute: 'app_admission_pre_index'
        ));
    }

    #[Route('/{id}/view', name: 'view', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function view(int $id): Response
    {
        /** @var AdmissionRecord|null $record */
        $record = $this->entityManager->find(AdmissionRecord::class, $id);
        if (!$record instanceof AdmissionRecord) {
            throw $this->createNotFoundException('Admisión no encontrada.');
        }

        $lookups = $this->admissionService->resolveAdmissionLookups($record);

        return $this->render('admission/view.html.twig', [
            'admission_id' => $record->getId(),
            'admission_type' => $record->getAdmissionType(),
            'admission_record' => $record,
            'admission_lookups' => $lookups,
        ]);
    }

    private function buildSearchViewData(Request $request, string $pageTitle, string $searchActionRoute): array
    {
        $rawType = (string) $request->query->get('identification_type', '');
        $initialTypeId = ctype_digit($rawType) ? (int) $rawType : 0;

        $identificationTypes = $this->patientSearchService->getActiveIdentificationTypes();

        $identificationChoices = ['Todos' => 0];
        $rutTypeId = 0;
        foreach ($identificationTypes as $type) {
            $identificationChoices[$type->getName()] = $type->getId();
            if (mb_strtolower((string) $type->getName()) === 'rut') {
                $rutTypeId = (int) $type->getId();
            }
        }

        $form = $this->createForm(PatientSearchType::class, [
            'identification_type' => $initialTypeId,
            'q' => trim((string) $request->query->get('q', '')),
        ], [
            'identification_choices' => $identificationChoices,
            'action' => $this->generateUrl($searchActionRoute),
        ]);
        $form->handleRequest($request);

        $formData = $form->getData();
        $searchTerm = trim((string) ($formData['q'] ?? ''));
        $selectedTypeId = $initialTypeId;
        if (isset($formData['identification_type']) && ctype_digit((string) $formData['identification_type'])) {
            $selectedTypeId = (int) $formData['identification_type'];
        }
        $searched = $searchTerm !== '';
        $patients = $this->patientSearchService->searchPatients($searchTerm, $selectedTypeId, 20);

        return [
            'page_title' => $pageTitle,
            'search_action_route' => $searchActionRoute,
            'search_term' => $searchTerm,
            'selected_type_id' => $selectedTypeId,
            'patients' => $patients,
            'searched' => $searched,
            'search_form' => $form->createView(),
            'rut_type_id' => $rutTypeId,
        ];
    }
}
