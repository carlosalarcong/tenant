<?php

namespace App\Controller\Nursing;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\AdmissionRecord;
use App\Entity\Tenant\NursingReturn;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nursing', name: 'app_nursing_')]
class NursingReturnController extends AbstractTenantAwareController
{
    public function __construct(private TenantEntityManager $entityManager) {}

    #[Route('/patient/{id}/return/create', name: 'patient_return_create', methods: ['GET', 'POST'], requirements: ['id' => '\\d+'])]
    public function create(int $id, Request $request): Response
    {
        $record = $this->findAdmissionRecord($id);

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('nursing_return_create', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }
            $return = (new NursingReturn())
                ->setAdmissionRecord($record)
                ->setItemName((string) $request->request->get('itemName', ''))
                ->setQuantity(max(1, (int) $request->request->get('quantity', 1)))
                ->setReason((string) $request->request->get('reason', ''));

            $this->entityManager->persist($return);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_nursing_patient_returns', ['id' => $id]);
        }

        return $this->render('nursing/patient/forms/return_form.html.twig', ['admission_id' => $id]);
    }

    #[Route('/patient/{id}/return/{rid}/cancel', name: 'patient_return_cancel', methods: ['POST'], requirements: ['id' => '\\d+', 'rid' => '\\d+'])]
    public function cancel(int $id, int $rid, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('nursing_return_cancel', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $return = $this->entityManager->find(NursingReturn::class, $rid);
        if ($return instanceof NursingReturn) {
            $return->setStatus('cancelled');
            $return->setCancelledAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_nursing_patient_returns', ['id' => $id]);
    }

    private function findAdmissionRecord(int $id): AdmissionRecord
    {
        $record = $this->entityManager->find(AdmissionRecord::class, $id);
        if (!$record instanceof AdmissionRecord) {
            throw $this->createNotFoundException('Admisión no encontrada.');
        }

        return $record;
    }
}
