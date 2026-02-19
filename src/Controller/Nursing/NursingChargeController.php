<?php

namespace App\Controller\Nursing;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\AdmissionRecord;
use App\Service\Nursing\NursingChargeService;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nursing', name: 'app_nursing_')]
class NursingChargeController extends AbstractTenantAwareController
{
    public function __construct(
        private TenantEntityManager $entityManager,
        private NursingChargeService $nursingChargeService
    ) {}

    #[Route('/patient/{id}/charge/articles', name: 'patient_charge_articles', methods: ['GET', 'POST'], requirements: ['id' => '\\d+'])]
    public function chargeArticles(int $id, Request $request): Response
    {
        $record = $this->findAdmissionRecord($id);

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('nursing_charge_articles', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }
            $items = $this->decodeItems((string) $request->request->get('items', '[]'));
            $this->nursingChargeService->chargeArticles($record, $items);
            return $this->redirectToRoute('app_nursing_patient_charges', ['id' => $id]);
        }

        return $this->render('nursing/patient/forms/charge_articles_form.html.twig', ['admission_id' => $id]);
    }

    #[Route('/patient/{id}/charge/interventions', name: 'patient_charge_interventions', methods: ['GET', 'POST'], requirements: ['id' => '\\d+'])]
    public function chargeInterventions(int $id, Request $request): Response
    {
        $record = $this->findAdmissionRecord($id);

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('nursing_charge_interventions', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }
            $items = $this->decodeItems((string) $request->request->get('items', '[]'));
            $this->nursingChargeService->chargeInterventions($record, $items);
            return $this->redirectToRoute('app_nursing_patient_charges', ['id' => $id]);
        }

        return $this->render('nursing/patient/forms/charge_interventions_form.html.twig', ['admission_id' => $id]);
    }

    #[Route('/patient/{id}/charge/packages', name: 'patient_charge_packages', methods: ['GET', 'POST'], requirements: ['id' => '\\d+'])]
    public function chargePackages(int $id, Request $request): Response
    {
        $record = $this->findAdmissionRecord($id);

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('nursing_charge_packages', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }
            $packages = $this->decodeItems((string) $request->request->get('items', '[]'));
            $this->nursingChargeService->chargePackages($record, $packages);
            return $this->redirectToRoute('app_nursing_patient_charges', ['id' => $id]);
        }

        return $this->render('nursing/patient/forms/charge_packages_form.html.twig', ['admission_id' => $id]);
    }

    #[Route('/patient/{id}/charge/{cid}/cancel', name: 'patient_charge_cancel', methods: ['POST'], requirements: ['id' => '\\d+', 'cid' => '\\d+'])]
    public function cancelCharge(int $id, int $cid, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('nursing_charge_cancel', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $type = (string) $request->request->get('type', 'article');
        $this->nursingChargeService->cancelCharge($cid, $type);

        return $this->redirectToRoute('app_nursing_patient_charges', ['id' => $id]);
    }

    private function findAdmissionRecord(int $id): AdmissionRecord
    {
        $record = $this->entityManager->find(AdmissionRecord::class, $id);
        if (!$record instanceof AdmissionRecord) {
            throw $this->createNotFoundException('Admisión no encontrada.');
        }

        return $record;
    }

    private function decodeItems(string $raw): array
    {
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}
