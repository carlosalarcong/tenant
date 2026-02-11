<?php

namespace App\Controller\Admission;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\AdmissionRecord;
use App\Entity\Tenant\Patient;
use App\Form\Admission\AdmissionStep2Type;
use App\Repository\Tenant\BranchRepository;
use App\Repository\Tenant\PaymentMethodRepository;
use App\Service\Admission\AdmissionService;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admission/wizard', name: 'app_admission_wizard_')]
class AdmissionWizardController extends AbstractTenantAwareController
{
    public function __construct(
        private TenantEntityManager $entityManager,
        private BranchRepository $branchRepository,
        private PaymentMethodRepository $paymentMethodRepository,
        private AdmissionService $admissionService
    ) {}

    #[Route('/step1/{patientId}', name: 'step1', methods: ['GET', 'POST'])]
    public function step1(Request $request, int $patientId): Response
    {
        $patient = $this->entityManager->find(Patient::class, $patientId);
        if (!$patient instanceof Patient) {
            $patient = $this->entityManager->createQueryBuilder()
                ->select('p')
                ->from(Patient::class, 'p')
                ->where('IDENTITY(p.person) = :personId')
                ->setParameter('personId', $patientId)
                ->orderBy('p.id', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        }

        if (!$patient instanceof Patient) {
            $this->addFlash('danger', 'Paciente no encontrado.');
            return $this->redirectToRoute('app_admission_hospitalization_index');
        }

        $admissionType = (string) $request->query->get('type', 'hospitalaria');
        if (!in_array($admissionType, ['hospitalaria', 'pre'], true)) {
            $admissionType = 'hospitalaria';
        }
        $wizardData = $request->getSession()->get('admission_wizard', []);
        $recordId = isset($wizardData['admission_record_id']) ? (int) $wizardData['admission_record_id'] : 0;
        $samePatient = isset($wizardData['patient_id']) && (int) $wizardData['patient_id'] === $patient->getId();
        $record = $recordId > 0 ? $this->entityManager->find(AdmissionRecord::class, $recordId) : null;

        if (!$samePatient || !$record instanceof AdmissionRecord) {
            $record = $this->admissionService->createDraftAdmission($patient, $admissionType);
            $wizardData = [
                'patient_id' => $patient->getId(),
                'admission_record_id' => $record->getId(),
                'admission_type' => $admissionType,
            ];
            $request->getSession()->set('admission_wizard', $wizardData);
        }

        return $this->redirectToRoute('app_admission_wizard_step2');
    }

    #[Route('/step2', name: 'step2', methods: ['GET', 'POST'])]
    public function step2(Request $request): Response
    {
        $wizardData = $request->getSession()->get('admission_wizard', []);
        if (empty($wizardData['patient_id']) || empty($wizardData['admission_record_id'])) {
            return $this->redirectToRoute('app_admission_hospitalization_index');
        }

        /** @var AdmissionRecord|null $record */
        $record = $this->entityManager->find(AdmissionRecord::class, (int) $wizardData['admission_record_id']);
        if (!$record instanceof AdmissionRecord) {
            $this->addFlash('danger', 'No se encontró la admisión en curso.');
            return $this->redirectToRoute('app_admission_hospitalization_index');
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

            if (!$this->admissionService->assignFinancialData($record, $payerId, $agreementId)) {
                $this->addFlash('danger', 'El convenio seleccionado no existe o no corresponde al financiador.');
                return $this->render('admission/wizard/step2.html.twig', [
                    'wizard' => $wizardData,
                    'branches' => $branches,
                    'form' => $form->createView(),
                ]);
            }
            if (!$this->admissionService->assignLocationData($record, $serviceId, $bedId)) {
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
        if (empty($wizardData['patient_id']) || empty($wizardData['admission_record_id'])) {
            return $this->redirectToRoute('app_admission_hospitalization_index');
        }

        /** @var AdmissionRecord|null $record */
        $record = $this->entityManager->find(AdmissionRecord::class, (int) $wizardData['admission_record_id']);
        if (!$record instanceof AdmissionRecord) {
            $this->addFlash('danger', 'No se encontró la admisión en curso.');
            return $this->redirectToRoute('app_admission_hospitalization_index');
        }

        $paymentMethods = $this->paymentMethodRepository->findForAdmissionFinancialSafeguard();

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('admission_step3_financial', (string) $request->request->get('_token'))) {
                $this->addFlash('danger', 'Token CSRF inválido. Recarga la página e inténtalo nuevamente.');
                return $this->render('admission/wizard/step3.html.twig', [
                    'wizard' => $wizardData,
                    'payment_methods' => $paymentMethods,
                    'posted_methods' => [],
                ]);
            }

            /** @var array<string, array<string, string>> $methodsInput */
            $methodsInput = (array) $request->request->get('methods', []);
            $selectedMethods = [];

            foreach ($paymentMethods as $paymentMethod) {
                $id = (string) $paymentMethod['id'];
                $rawEnabled = $methodsInput[$id]['enabled'] ?? '';
                $rawAmount = $methodsInput[$id]['amount'] ?? '';
                $enabled = in_array((string) $rawEnabled, ['1', 'on', 'true'], true);
                $amount = is_numeric((string) $rawAmount) ? (float) $rawAmount : 0.0;

                if (!$enabled) {
                    continue;
                }
                if ($amount <= 0) {
                    $this->addFlash('danger', sprintf('Debes ingresar monto para "%s".', $paymentMethod['name']));
                    return $this->render('admission/wizard/step3.html.twig', [
                        'wizard' => $wizardData,
                        'payment_methods' => $paymentMethods,
                        'posted_methods' => $methodsInput,
                    ]);
                }

                $selectedMethods[] = [
                    'id' => (int) $id,
                    'name' => $paymentMethod['name'],
                    'amount' => $amount,
                ];
            }

            if ([] === $selectedMethods) {
                $this->addFlash('danger', 'Debes indicar al menos un medio de pago para continuar.');
                return $this->render('admission/wizard/step3.html.twig', [
                    'wizard' => $wizardData,
                    'payment_methods' => $paymentMethods,
                    'posted_methods' => $methodsInput,
                ]);
            }

            $wizardData['payment_methods'] = $selectedMethods;
            $request->getSession()->set('admission_wizard', $wizardData);
            $this->admissionService->finalizeAdmission($record);
            $admissionId = $record->getId();
            $request->getSession()->remove('admission_wizard');

            return $this->redirectToRoute('app_admission_view', [
                'id' => $admissionId,
            ]);
        }

        return $this->render('admission/wizard/step3.html.twig', [
            'wizard' => $wizardData,
            'payment_methods' => $paymentMethods,
            'posted_methods' => [],
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
}
