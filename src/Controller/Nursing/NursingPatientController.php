<?php

namespace App\Controller\Nursing;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\AdmissionRecord;
use App\Repository\Tenant\NursingChargeRepository;
use App\Repository\Tenant\NursingConcurrencyRepository;
use App\Repository\Tenant\NursingOrderRepository;
use App\Repository\Tenant\NursingPrescriptionRepository;
use App\Repository\Tenant\NursingReturnRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nursing', name: 'app_nursing_')]
class NursingPatientController extends AbstractTenantAwareController
{
    public function __construct(
        private TenantEntityManager $entityManager,
        private NursingConcurrencyRepository $nursingConcurrencyRepository,
        private NursingPrescriptionRepository $nursingPrescriptionRepository,
        private NursingOrderRepository $nursingOrderRepository,
        private NursingChargeRepository $nursingChargeRepository,
        private NursingReturnRepository $nursingReturnRepository
    ) {}

    #[Route('/patient/{id}', name: 'patient_show', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function show(int $id, Request $request): Response
    {
        $record = $this->findAdmissionRecord($id);
        $userId = $this->resolveUserId();
        $sessionId = $this->resolveSessionId($request);
        $lockAcquired = $this->nursingConcurrencyRepository->acquireLock($record->getId(), $userId, $sessionId);

        return $this->render('nursing/patient/show.html.twig', [
            'admission_record' => $record,
            'lock_acquired' => $lockAcquired,
            'session_id' => $sessionId,
        ]);
    }

    #[Route('/patient/{id}/unlock', name: 'patient_unlock', methods: ['DELETE'], requirements: ['id' => '\\d+'])]
    public function unlock(int $id, Request $request): JsonResponse
    {
        $sessionId = $this->resolveSessionId($request);
        $this->nursingConcurrencyRepository->releaseLock($id, $sessionId);

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/patient/{id}/prescriptions', name: 'patient_prescriptions', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function prescriptions(int $id): Response
    {
        $record = $this->findAdmissionRecord($id);

        return $this->render('nursing/patient/tabs/_prescriptions.html.twig', [
            'admission_record' => $record,
            'prescriptions' => $this->nursingPrescriptionRepository->findByAdmissionRecord($id),
        ]);
    }

    #[Route('/patient/{id}/summary', name: 'patient_summary', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function summary(int $id): Response
    {
        $record = $this->findAdmissionRecord($id);

        return $this->render('nursing/patient/tabs/_summary.html.twig', [
            'admission_record' => $record,
        ]);
    }

    #[Route('/patient/{id}/orders', name: 'patient_orders', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function orders(int $id): Response
    {
        $record = $this->findAdmissionRecord($id);

        return $this->render('nursing/patient/tabs/_orders.html.twig', [
            'admission_record' => $record,
            'orders' => $this->nursingOrderRepository->findByAdmissionRecord($id),
        ]);
    }

    #[Route('/patient/{id}/charges', name: 'patient_charges', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function charges(int $id): Response
    {
        $record = $this->findAdmissionRecord($id);

        return $this->render('nursing/patient/tabs/_charges.html.twig', [
            'admission_record' => $record,
            'charges' => $this->nursingChargeRepository->findByAdmissionRecord($id),
        ]);
    }

    #[Route('/patient/{id}/returns', name: 'patient_returns', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function returns(int $id): Response
    {
        $record = $this->findAdmissionRecord($id);

        return $this->render('nursing/patient/tabs/_returns.html.twig', [
            'admission_record' => $record,
            'returns' => $this->nursingReturnRepository->findByAdmissionRecord($id),
        ]);
    }

    #[Route('/patient/{id}/forms', name: 'patient_forms', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function forms(int $id): Response
    {
        $record = $this->findAdmissionRecord($id);

        return $this->render('nursing/patient/tabs/_forms.html.twig', [
            'admission_record' => $record,
        ]);
    }

    private function findAdmissionRecord(int $id): AdmissionRecord
    {
        $record = $this->entityManager->find(AdmissionRecord::class, $id);
        if (!$record instanceof AdmissionRecord) {
            throw $this->createNotFoundException('Paciente/admisión no encontrada.');
        }

        return $record;
    }

    private function resolveUserId(): int
    {
        $user = $this->getUser();
        if (is_object($user) && method_exists($user, 'getId')) {
            $id = (int) $user->getId();
            if ($id > 0) {
                return $id;
            }
        }

        return 1;
    }

    private function resolveSessionId(Request $request): string
    {
        $header = trim((string) $request->headers->get('X-Nursing-Session', ''));
        if ($header !== '') {
            return substr($header, 0, 128);
        }

        return substr(hash('sha256', implode('|', [
            (string) $this->resolveUserId(),
            (string) $request->getClientIp(),
            (string) $request->headers->get('User-Agent', ''),
        ])), 0, 64);
    }
}
