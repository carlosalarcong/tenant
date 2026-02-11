<?php

namespace App\Controller\Admission;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\AdmissionRecord;
use App\Entity\Tenant\Person;
use App\Form\Admission\AdmissionStep2Type;
use App\Form\Admission\AdmissionStep3Type;
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
        private AdmissionService $admissionService
    ) {}

    #[Route('/step1/{patientId}', name: 'step1', methods: ['GET', 'POST'])]
    public function step1(Request $request, int $patientId): Response
    {
        $patient = $this->entityManager->find(Person::class, $patientId);
        if (!$patient instanceof Person) {
            $this->addFlash('danger', 'Paciente no encontrado.');
            return $this->redirectToRoute('app_admission_hospitalization_index');
        }

        $admissionType = (string) $request->query->get('type', 'hospitalaria');
        if (!in_array($admissionType, ['hospitalaria', 'pre'], true)) {
            $admissionType = 'hospitalaria';
        }

        if ($request->isMethod('POST')) {
            $record = $this->admissionService->createDraftAdmission($patient, $admissionType);

            $request->getSession()->set('admission_wizard', [
                'patient_id' => $patientId,
                'admission_record_id' => $record->getId(),
                'admission_type' => $admissionType,
                'step1_confirmed' => true,
            ]);

            return $this->redirectToRoute('app_admission_wizard_step2');
        }

        return $this->render('admission/wizard/step1.html.twig', [
            'patient_id' => $patientId,
            'patient' => $patient,
            'admission_type' => $admissionType,
        ]);
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

        $step2Defaults = [
            'branch' => 1,
            'payer' => isset($wizardData['payer']) ? (int) $wizardData['payer'] : null,
            'agreement' => isset($wizardData['agreement']) ? (int) $wizardData['agreement'] : null,
        ];
        $form = $this->createForm(AdmissionStep2Type::class, $step2Defaults);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $payerId = (int) $form->get('payer')->getData();
            $agreementId = (int) $form->get('agreement')->getData();

            if (!$this->admissionService->assignFinancialData($record, $payerId, $agreementId)) {
                $this->addFlash('danger', 'El convenio seleccionado no existe o no corresponde al financiador.');
                return $this->render('admission/wizard/step2.html.twig', [
                    'wizard' => $wizardData,
                    'form' => $form->createView(),
                ]);
            }

            $wizardData['payer'] = (string) $payerId;
            $wizardData['agreement'] = (string) $agreementId;
            $request->getSession()->set('admission_wizard', $wizardData);

            return $this->redirectToRoute('app_admission_wizard_step3');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'Debes seleccionar financiador y convenio.');
        }

        return $this->render('admission/wizard/step2.html.twig', [
            'wizard' => $wizardData,
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

        $step3Defaults = [
            'branch' => 1,
            'service' => isset($wizardData['service']) ? (int) $wizardData['service'] : null,
            'bed' => isset($wizardData['bed']) ? (int) $wizardData['bed'] : null,
        ];
        $form = $this->createForm(AdmissionStep3Type::class, $step3Defaults);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $serviceId = (int) $form->get('service')->getData();
            $bedId = (int) $form->get('bed')->getData();
            if (!$this->admissionService->assignLocationData($record, $serviceId, $bedId)) {
                $this->addFlash('danger', 'El servicio o la cama seleccionada no existen o están inactivos.');
                return $this->render('admission/wizard/step3.html.twig', [
                    'wizard' => $wizardData,
                    'form' => $form->createView(),
                ]);
            }

            $wizardData['service'] = (string) $serviceId;
            $wizardData['bed'] = (string) $bedId;
            $request->getSession()->set('admission_wizard', $wizardData);

            $admissionId = $record->getId();
            $request->getSession()->remove('admission_wizard');

            return $this->redirectToRoute('app_admission_view', [
                'id' => $admissionId,
            ]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'Debes seleccionar servicio y cama.');
        }

        return $this->render('admission/wizard/step3.html.twig', [
            'wizard' => $wizardData,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/complete/{admissionId}', name: 'complete', methods: ['GET'])]
    public function complete(int $admissionId): Response
    {
        return $this->render('admission/wizard/complete.html.twig', [
            'admission_id' => $admissionId,
        ]);
    }
}
