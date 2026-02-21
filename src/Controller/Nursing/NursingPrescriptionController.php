<?php

namespace App\Controller\Nursing;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\AdmissionRecord;
use App\Entity\Tenant\NursingPrescription;
use App\Entity\Tenant\NursingPrescriptionItem;
use App\Entity\Tenant\Person;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nursing', name: 'app_nursing_')]
class NursingPrescriptionController extends AbstractTenantAwareController
{
    public function __construct(private TenantEntityManager $entityManager) {}

    #[Route('/patient/{id}/prescription/create', name: 'patient_prescription_create', methods: ['GET', 'POST'], requirements: ['id' => '\\d+'])]
    public function create(int $id, Request $request): Response
    {
        $record = $this->entityManager->find(AdmissionRecord::class, $id);
        if (!$record instanceof AdmissionRecord) {
            throw $this->createNotFoundException('Admisión no encontrada.');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('nursing_prescription_create', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }
            $prescribedById = (int) $request->request->get('prescribedById', 0);
            $prescribedBy = $this->entityManager->find(Person::class, $prescribedById);
            if (!$prescribedBy instanceof Person) {
                $prescribedBy = $record->getPatient()?->getPerson();
            }
            if (!$prescribedBy instanceof Person) {
                throw $this->createNotFoundException('No se encontró persona para registrar la receta.');
            }

            $prescription = (new NursingPrescription())
                ->setAdmissionRecord($record)
                ->setPrescribedBy($prescribedBy)
                ->setStatus('active');

            $item = (new NursingPrescriptionItem())
                ->setArticleCode((string) $request->request->get('articleCode', 'NA'))
                ->setArticleName((string) $request->request->get('articleName', 'Sin nombre'))
                ->setDose((string) $request->request->get('dose', '1'))
                ->setRoute((string) $request->request->get('route', 'oral'))
                ->setFrequency((string) $request->request->get('frequency', 'cada 8h'))
                ->setQuantity(max(1, (int) $request->request->get('quantity', 1)));

            $prescription->addItem($item);
            $this->entityManager->persist($prescription);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_nursing_patient_prescriptions', ['id' => $id]);
        }

        return $this->render('nursing/patient/forms/prescription_form.html.twig', [
            'admission_id' => $id,
        ]);
    }

    #[Route('/patient/{id}/prescription/{pid}/edit', name: 'patient_prescription_edit', methods: ['GET', 'POST'], requirements: ['id' => '\\d+', 'pid' => '\\d+'])]
    public function edit(int $id, int $pid, Request $request): Response
    {
        $prescription = $this->entityManager->find(NursingPrescription::class, $pid);
        if (!$prescription instanceof NursingPrescription) {
            throw $this->createNotFoundException('Receta no encontrada.');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('nursing_prescription_edit', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }
            $prescription->setStatus((string) $request->request->get('status', $prescription->getStatus()));
            $this->entityManager->flush();
            return $this->redirectToRoute('app_nursing_patient_prescriptions', ['id' => $id]);
        }

        return $this->render('nursing/patient/forms/prescription_edit_form.html.twig', [
            'admission_id' => $id,
            'prescription' => $prescription,
        ]);
    }

    #[Route('/patient/{id}/prescription/{pid}/cancel', name: 'patient_prescription_cancel', methods: ['POST'], requirements: ['id' => '\\d+', 'pid' => '\\d+'])]
    public function cancel(int $id, int $pid, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('nursing_prescription_cancel', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $prescription = $this->entityManager->find(NursingPrescription::class, $pid);
        if ($prescription instanceof NursingPrescription) {
            $prescription->setStatus('cancelled');
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_nursing_patient_prescriptions', ['id' => $id]);
    }

    #[Route('/patient/{id}/prescription/{pid}/complement', name: 'patient_prescription_complement', methods: ['POST'], requirements: ['id' => '\\d+', 'pid' => '\\d+'])]
    public function complement(int $id, int $pid, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('nursing_prescription_complement', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $prescription = $this->entityManager->find(NursingPrescription::class, $pid);
        if ($prescription instanceof NursingPrescription) {
            $prescription->setComplement((string) $request->request->get('complement', ''));
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_nursing_patient_prescriptions', ['id' => $id]);
    }
}
