<?php

namespace App\Controller\Admission;

use App\Controller\AbstractTenantAwareController;
use App\DTO\Revenue\Payment\PaymentBatchDTO;
use App\Entity\Tenant\AdmissionRecord;
use App\Entity\Tenant\Person;
use App\Form\Admission\AdmissionStep2Type;
use App\Repository\Tenant\BranchRepository;
use App\Service\Admission\AdmissionService;
use App\Service\Revenue\Payment\PaymentBatchProcessor;
use App\Service\Revenue\Payment\PaymentMethodConfigRegistry;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admission/wizard', name: 'app_admission_wizard_')]
class AdmissionWizardController extends AbstractTenantAwareController
{
    public function __construct(
        private TenantEntityManager $entityManager,
        private BranchRepository $branchRepository,
        private AdmissionService $admissionService,
        private PaymentMethodConfigRegistry $paymentMethodConfigRegistry,
        private PaymentBatchProcessor $paymentBatchProcessor,
        private FormFactoryInterface $formFactory
    ) {}

    #[Route('/step1/{patientId}', name: 'step1', methods: ['GET', 'POST'])]
    public function step1(Request $request, int $patientId): Response
    {
        $person = $this->entityManager->find(Person::class, $patientId);
        if (!$person instanceof Person) {
            $this->addFlash('danger', 'Persona no encontrada.');
            if ($this->isWizardFrameRequest($request)) {
                return $this->renderWizardErrorFrame('No existe un registro de persona válido para iniciar la admisión.');
            }
            return $this->safeRedirect($request, 'app_admission_hospitalization_index');
        }

        $blockingAdmission = $this->admissionService->findBlockingAdmissionForPerson((int) $person->getId());
        if ($blockingAdmission instanceof AdmissionRecord) {
            $this->addFlash(
                'danger',
                sprintf('La persona ya tiene una admisión activa (#%d).', (int) $blockingAdmission->getId())
            );

            return $this->safeRedirect($request, 'app_admission_view', [
                'id' => (int) $blockingAdmission->getId(),
            ]);
        }

        $admissionType = (string) $request->query->get('type', 'hospitalaria');
        if (!in_array($admissionType, ['hospitalaria', 'pre'], true)) {
            $admissionType = 'hospitalaria';
        }
        $wizardData = [
            'person_id' => $person->getId(),
            'admission_type' => $admissionType,
        ];
        $request->getSession()->set('admission_wizard', $wizardData);

        return $this->redirectToRoute('app_admission_wizard_step2');
    }

    #[Route('/step2', name: 'step2', methods: ['GET', 'POST'])]
    public function step2(Request $request): Response
    {
        $wizardData = $request->getSession()->get('admission_wizard', []);
        if (empty($wizardData['person_id'])) {
            if ($this->isWizardFrameRequest($request)) {
                return $this->renderWizardErrorFrame('La sesión del asistente de admisión expiró. Inicia nuevamente desde la búsqueda.');
            }
            return $this->safeRedirect($request, 'app_admission_hospitalization_index');
        }

        $branches = $this->loadBranches();
        $defaultBranchId = isset($wizardData['branch']) ? (int) $wizardData['branch'] : ($branches[0]['id'] ?? 1);

        $step2Defaults = [
            'branch' => $defaultBranchId,
            'professional' => isset($wizardData['professional']) ? (int) $wizardData['professional'] : null,
            'specialty' => isset($wizardData['specialty']) ? (int) $wizardData['specialty'] : null,
            'origin' => isset($wizardData['origin']) ? (int) $wizardData['origin'] : null,
            'payer' => isset($wizardData['payer']) ? (int) $wizardData['payer'] : null,
            'agreement' => isset($wizardData['agreement']) ? (int) $wizardData['agreement'] : null,
            'service' => isset($wizardData['service']) ? (int) $wizardData['service'] : null,
            'bed' => isset($wizardData['bed']) ? (int) $wizardData['bed'] : null,
            'referralDoctor' => (string) ($wizardData['referralDoctor'] ?? ''),
            'emergencyContact' => (string) ($wizardData['emergencyContact'] ?? ''),
            'emergencyPhone' => (string) ($wizardData['emergencyPhone'] ?? ''),
            'childrenCount' => isset($wizardData['childrenCount']) ? (int) $wizardData['childrenCount'] : null,
            'tutorDocument' => (string) ($wizardData['tutorDocument'] ?? ''),
            'tutorName' => (string) ($wizardData['tutorName'] ?? ''),
            'observations' => (string) ($wizardData['observations'] ?? ''),
            'medicalOrder' => filter_var($wizardData['medicalOrder'] ?? false, FILTER_VALIDATE_BOOL),
        ];
        $form = $this->createForm(AdmissionStep2Type::class, $step2Defaults);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $payerId = (int) $form->get('payer')->getData();
            $agreementId = (int) $form->get('agreement')->getData();
            $serviceId = (int) $form->get('service')->getData();
            $bedId = (int) $form->get('bed')->getData();

            if (!$this->admissionService->validateFinancialData($payerId, $agreementId)) {
                $this->addFlash('danger', 'El convenio seleccionado no existe o no corresponde al financiador.');
                return $this->render('admission/wizard/step2.html.twig', [
                    'wizard' => $wizardData,
                    'branches' => $branches,
                    'form' => $form->createView(),
                ]);
            }
            if (!$this->admissionService->validateLocationData($serviceId, $bedId)) {
                $this->addFlash('danger', 'El servicio o la cama seleccionada no existen o están inactivos.');
                return $this->render('admission/wizard/step2.html.twig', [
                    'wizard' => $wizardData,
                    'branches' => $branches,
                    'form' => $form->createView(),
                ]);
            }

            $wizardData['branch'] = (string) ((int) $form->get('branch')->getData());
            $wizardData['professional'] = (string) ((int) ($form->get('professional')->getData() ?? 0));
            $wizardData['specialty'] = (string) ((int) ($form->get('specialty')->getData() ?? 0));
            $wizardData['origin'] = (string) ((int) ($form->get('origin')->getData() ?? 0));
            $wizardData['payer'] = (string) $payerId;
            $wizardData['agreement'] = (string) $agreementId;
            $wizardData['service'] = (string) $serviceId;
            $wizardData['bed'] = (string) $bedId;
            $wizardData['referralDoctor'] = (string) ($form->get('referralDoctor')->getData() ?? '');
            $wizardData['emergencyContact'] = (string) ($form->get('emergencyContact')->getData() ?? '');
            $wizardData['emergencyPhone'] = (string) ($form->get('emergencyPhone')->getData() ?? '');
            $wizardData['childrenCount'] = (string) ((int) ($form->get('childrenCount')->getData() ?? 0));
            $wizardData['tutorDocument'] = (string) ($form->get('tutorDocument')->getData() ?? '');
            $wizardData['tutorName'] = (string) ($form->get('tutorName')->getData() ?? '');
            $wizardData['observations'] = (string) ($form->get('observations')->getData() ?? '');
            $wizardData['medicalOrder'] = ($form->get('medicalOrder')->getData() ?? false) ? '1' : '0';
            $request->getSession()->set('admission_wizard', $wizardData);

            if ($this->isWizardFrameRequest($request)) {
                return $this->render('admission/wizard/step3.html.twig', [
                    'wizard' => $wizardData,
                    'payment_methods' => $this->paymentMethodConfigRegistry->all(),
                    'initial_rows' => $this->createInitialPaymentRows(),
                ]);
            }

            return $this->redirectToRoute('app_admission_wizard_step3');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'Debes completar financiador, convenio, servicio y cama.');
        }

        return $this->render('admission/wizard/step2.html.twig', [
            'wizard' => $wizardData,
            'branches' => $branches,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/step3', name: 'step3', methods: ['GET', 'POST'])]
    public function step3(Request $request): Response
    {
        $wizardData = $request->getSession()->get('admission_wizard', []);
        if (empty($wizardData['person_id'])) {
            if ($this->isWizardFrameRequest($request)) {
                return $this->renderWizardErrorFrame('La sesión del asistente de admisión expiró. Inicia nuevamente desde la búsqueda.');
            }
            return $this->safeRedirect($request, 'app_admission_hospitalization_index');
        }

        $paymentMethods = $this->paymentMethodConfigRegistry->all();
        $initialRows = $this->createInitialPaymentRows();

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('admission_step3_financial', (string) $request->request->get('_token'))) {
                $this->addFlash('danger', 'Token CSRF inválido. Recarga la página e inténtalo nuevamente.');
                return $this->render('admission/wizard/step3.html.twig', [
                    'wizard' => $wizardData,
                    'payment_methods' => $paymentMethods,
                    'initial_rows' => $initialRows,
                ]);
            }

            $paymentBatchInput = $request->request->all('payment_batch');
            if (!is_array($paymentBatchInput)) {
                $paymentBatchInput = [];
            }

            $paymentBatch = $this->paymentBatchProcessor->process($paymentBatchInput);
            if (!$paymentBatch->isValid()) {
                foreach ($this->collectBatchErrors($paymentBatch) as $error) {
                    $this->addFlash('danger', $error);
                }

                return $this->render('admission/wizard/step3.html.twig', [
                    'wizard' => $wizardData,
                    'payment_methods' => $paymentMethods,
                    'initial_rows' => $initialRows,
                ]);
            }

            $wizardData['payment_batch'] = $this->serializeBatch($paymentBatch);
            $record = $this->admissionService->createAdmissionFromWizard(
                (int) $wizardData['person_id'],
                (string) ($wizardData['admission_type'] ?? 'hospitalaria'),
                (int) ($wizardData['payer'] ?? 0),
                (int) ($wizardData['agreement'] ?? 0),
                (int) ($wizardData['service'] ?? 0),
                (int) ($wizardData['bed'] ?? 0),
            );

            if (!$record instanceof AdmissionRecord) {
                $blockingReason = $this->admissionService->getWizardCreationBlockingReason(
                    (int) $wizardData['person_id'],
                    (int) ($wizardData['payer'] ?? 0),
                    (int) ($wizardData['agreement'] ?? 0),
                    (int) ($wizardData['service'] ?? 0),
                    (int) ($wizardData['bed'] ?? 0),
                );
                $this->addFlash('danger', $blockingReason ?? 'No fue posible crear el registro de paciente/admisión.');
                return $this->render('admission/wizard/step3.html.twig', [
                    'wizard' => $wizardData,
                    'payment_methods' => $paymentMethods,
                    'initial_rows' => $initialRows,
                ]);
            }

            $admissionId = $record->getId();
            $request->getSession()->remove('admission_wizard');

            return $this->safeRedirect($request, 'app_admission_view', [
                'id' => $admissionId,
            ]);
        }

        return $this->render('admission/wizard/step3.html.twig', [
            'wizard' => $wizardData,
            'payment_methods' => $paymentMethods,
            'initial_rows' => $initialRows,
        ]);
    }

    #[Route('/complete/{admissionId}', name: 'complete', methods: ['GET'])]
    public function complete(int $admissionId): Response
    {
        return $this->render('admission/wizard/complete.html.twig', [
            'admission_id' => $admissionId,
        ]);
    }

    /**
     * @return array<int, array{id:int,name:string}>
     */
    private function loadBranches(): array
    {
        return $this->branchRepository->findActiveChoices();
    }

    /**
     * @return array<string, FormView>
     */
    private function createInitialPaymentRows(): array
    {
        $rows = [];
        foreach ($this->paymentMethodConfigRegistry->all() as $methodCode => $config) {
            // Symfony no permite [] en el nombre interno del form; se fuerza full_name en Twig.
            $form = $this->formFactory->createNamed(
                sprintf('payment_batch_rows_%s_%d', $methodCode, 0),
                $config['form_type'],
                null,
                ['csrf_protection' => false]
            );

            $rows[$methodCode] = $form->createView();
        }

        return $rows;
    }

    /**
     * @return array<string>
     */
    private function collectBatchErrors(PaymentBatchDTO $batch): array
    {
        $errors = $batch->getErrors();
        foreach ($batch->getRowsFlat() as $row) {
            foreach ($row->getErrors() as $error) {
                $errors[] = $error;
            }
        }

        return $errors;
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function serializeBatch(PaymentBatchDTO $batch): array
    {
        $normalized = [];
        foreach ($batch->getRowsByMethod() as $methodCode => $rows) {
            foreach ($rows as $index => $row) {
                $normalized[$methodCode][$index] = $row->getPayload();
            }
        }

        return $normalized;
    }

    /**
     * Blindaje para navegación Turbo Frame: fuerza visita completa cuando hay redirect.
     *
     * @param array<string, mixed> $parameters
     */
    private function safeRedirect(Request $request, string $route, array $parameters = []): RedirectResponse
    {
        $response = $this->redirectToRoute($route, $parameters);

        if ($request->headers->has('Turbo-Frame')) {
            $url = $this->generateUrl($route, $parameters);
            $response->headers->set('Turbo-Location', $url);
            $response->headers->set('Turbo-Visit-Control', 'reload');
        }

        return $response;
    }

    private function isWizardFrameRequest(Request $request): bool
    {
        return 'admission-wizard-content' === (string) $request->headers->get('Turbo-Frame', '');
    }

    private function renderWizardErrorFrame(string $message): Response
    {
        return $this->render('admission/wizard/_error_frame.html.twig', [
            'message' => $message,
        ]);
    }
}
