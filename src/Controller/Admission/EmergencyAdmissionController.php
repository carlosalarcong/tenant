<?php

namespace App\Controller\Admission;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\AdmissionRecord;
use App\Entity\Tenant\Person;
use App\Form\Admission\PatientRegistrationUrgencyType;
use App\Service\Admission\AdmissionService;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admission/emergency', name: 'app_admission_emergency_')]
class EmergencyAdmissionController extends AbstractTenantAwareController
{
    public function __construct(
        private TenantEntityManager $entityManager,
        private AdmissionService $admissionService
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->render('admission/emergency/index.html.twig', [
            'page_title' => 'Admisión Urgencia',
        ]);
    }

    #[Route('/create/{patientId}', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, int $patientId): Response
    {
        $patient = $this->entityManager->find(Person::class, $patientId);
        if (!$patient instanceof Person) {
            $this->addFlash('danger', 'Paciente no encontrado para urgencia.');
            return $this->redirectToRoute('app_admission_emergency_index');
        }

        $form = $this->createForm(PatientRegistrationUrgencyType::class, [
            'triage' => null,
            'reason' => '',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array<string,mixed> $data */
            $data = $form->getData();
            $record = new AdmissionRecord();
            $record->setPerson($patient);
            $record->setAdmissionType('urgencia');
            $record->setStatus('completed');
            $record->setTriage(trim((string) ($data['triage'] ?? '')));
            $record->setConsultationReason(trim((string) ($data['reason'] ?? '')));

            $this->entityManager->persist($record);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admission_emergency_view', [
                'id' => $record->getId(),
            ]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'Debes completar triage y motivo de consulta.');
        }

        return $this->render('admission/emergency/_form.html.twig', [
            'patient_id' => $patientId,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/view', name: 'view', methods: ['GET'])]
    public function view(int $id): Response
    {
        /** @var AdmissionRecord|null $record */
        $record = $this->entityManager->find(AdmissionRecord::class, $id);
        if (!$record instanceof AdmissionRecord || $record->getAdmissionType() !== 'urgencia') {
            throw $this->createNotFoundException('Admisión de urgencia no encontrada.');
        }

        $lookups = $this->admissionService->resolveAdmissionLookups($record);

        return $this->render('admission/view.html.twig', [
            'admission_id' => $id,
            'admission_type' => 'urgencia',
            'admission_record' => $record,
            'admission_lookups' => $lookups,
        ]);
    }
}
