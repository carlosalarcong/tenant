<?php

namespace App\Controller\Nursing;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\AdmissionRecord;
use App\Entity\Tenant\NursingDischarge;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nursing', name: 'app_nursing_')]
class NursingPatientDischargeController extends AbstractTenantAwareController
{
    public function __construct(private TenantEntityManager $entityManager) {}

    #[Route('/patient/{id}/discharge/create', name: 'patient_discharge_create', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function create(int $id, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('nursing_discharge_create', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $record = $this->entityManager->find(AdmissionRecord::class, $id);
        if (!$record instanceof AdmissionRecord) {
            return $this->redirectToRoute('app_nursing_index');
        }

        $discharge = (new NursingDischarge())
            ->setAdmissionRecord($record)
            ->setDischargeType((string) $request->request->get('dischargeType', 'alta'))
            ->setConditionStatus((string) $request->request->get('conditionStatus', 'bueno'))
            ->setNotes((string) $request->request->get('notes', ''));

        $record->setStatus('discharged');
        if ($record->getBed()) {
            $record->getBed()->setStatus('available');
            $record->getBed()->setUpdatedAt(new \DateTimeImmutable());
            $record->setBed(null);
        }

        $this->entityManager->persist($discharge);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_nursing_patient_show', ['id' => $id]);
    }

    #[Route('/patient/{id}/discharge/cancel', name: 'patient_discharge_cancel', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function cancel(int $id, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('nursing_discharge_cancel', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $dischargeId = (int) $request->request->get('dischargeId', 0);
        $discharge = $this->entityManager->find(NursingDischarge::class, $dischargeId);
        if ($discharge instanceof NursingDischarge) {
            $discharge->setCancelledAt(new \DateTimeImmutable());
            $discharge->setCancelledByUserId($this->resolveUserId());

            $record = $discharge->getAdmissionRecord();
            if ($record instanceof AdmissionRecord) {
                $record->setStatus('admitted');
            }

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
