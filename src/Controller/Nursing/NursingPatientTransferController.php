<?php

namespace App\Controller\Nursing;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\AdmissionRecord;
use App\Entity\Tenant\NursingTransfer;
use App\Entity\Tenant\Service;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nursing', name: 'app_nursing_')]
class NursingPatientTransferController extends AbstractTenantAwareController
{
    public function __construct(private TenantEntityManager $entityManager) {}

    #[Route('/patient/{id}/transfer/create', name: 'patient_transfer_create', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function create(int $id, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('nursing_transfer_create', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $destinationId = (int) $request->request->get('destinationServiceId', 0);
        $record = $this->entityManager->find(AdmissionRecord::class, $id);
        $destination = $this->entityManager->find(Service::class, $destinationId);

        if (!$record instanceof AdmissionRecord || !$destination instanceof Service || !$record->getService() instanceof Service) {
            return $this->redirectToRoute('app_nursing_patient_show', ['id' => $id]);
        }

        $transfer = (new NursingTransfer())
            ->setAdmissionRecord($record)
            ->setOriginService($record->getService())
            ->setDestinationService($destination)
            ->setRequestedByUserId($this->resolveUserId())
            ->setStatus('pending')
            ->setNotes((string) $request->request->get('notes', ''));

        $this->entityManager->persist($transfer);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_nursing_patient_show', ['id' => $id]);
    }

    #[Route('/patient/{id}/transfer/cancel', name: 'patient_transfer_cancel', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function cancel(int $id, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('nursing_transfer_cancel', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $transferId = (int) $request->request->get('transferId', 0);
        $transfer = $this->entityManager->find(NursingTransfer::class, $transferId);
        if ($transfer instanceof NursingTransfer && $transfer->getStatus() === 'pending') {
            $transfer->setStatus('cancelled');
            $transfer->setCancelledAt(new \DateTimeImmutable());
            $transfer->setCancelledByUserId($this->resolveUserId());
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_nursing_patient_show', ['id' => $id]);
    }

    private function resolveUserId(): int
    {
        $user = $this->getUser();
        if (is_object($user) && method_exists($user, 'getId') && (int) $user->getId() > 0) {
            return (int) $user->getId();
        }

        return 1;
    }
}
