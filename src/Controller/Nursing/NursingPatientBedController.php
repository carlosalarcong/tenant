<?php

namespace App\Controller\Nursing;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\AdmissionRecord;
use App\Entity\Tenant\Bed;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nursing', name: 'app_nursing_')]
class NursingPatientBedController extends AbstractTenantAwareController
{
    public function __construct(private TenantEntityManager $entityManager) {}

    #[Route('/patient/{id}/bed/change', name: 'patient_bed_change', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function change(int $id, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('nursing_bed_change', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $bedId = (int) $request->request->get('bedId', 0);
        $record = $this->entityManager->find(AdmissionRecord::class, $id);
        $newBed = $this->entityManager->find(Bed::class, $bedId);

        if (!$record instanceof AdmissionRecord || !$newBed instanceof Bed || $newBed->getStatus() !== 'available') {
            return $this->redirectToRoute('app_nursing_patient_show', ['id' => $id]);
        }

        $oldBed = $record->getBed();
        if ($oldBed instanceof Bed) {
            $oldBed->setStatus('available');
            $oldBed->setUpdatedAt(new \DateTimeImmutable());
        }

        $record->setBed($newBed);
        $record->setStatus('admitted');
        $newBed->setStatus('occupied');
        $newBed->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->redirectToRoute('app_nursing_patient_show', ['id' => $id]);
    }
}
