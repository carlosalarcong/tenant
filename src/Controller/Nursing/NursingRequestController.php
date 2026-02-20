<?php

namespace App\Controller\Nursing;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\AdmissionRecord;
use App\Entity\Tenant\Bed;
use App\Repository\Tenant\AdmissionRecordRepository;
use App\Repository\Tenant\BedRepository;
use App\Repository\Tenant\NursingDischargeRepository;
use App\Repository\Tenant\NursingTransferRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nursing', name: 'app_nursing_')]
class NursingRequestController extends AbstractTenantAwareController
{
    public function __construct(
        private TenantEntityManager $entityManager,
        private AdmissionRecordRepository $admissionRecordRepository,
        private NursingTransferRepository $nursingTransferRepository,
        private NursingDischargeRepository $nursingDischargeRepository,
        private BedRepository $bedRepository
    ) {}

    #[Route('/request/admissions/{serviceId}', name: 'request_admissions', methods: ['GET'], requirements: ['serviceId' => '\\d+'])]
    public function admissions(int $serviceId): Response
    {
        return $this->render('nursing/request/admissions.html.twig', [
            'service_id' => $serviceId,
            'requests' => $this->admissionRecordRepository->findPendingListByService($serviceId),
        ]);
    }

    #[Route('/request/transfers/{serviceId}', name: 'request_transfers', methods: ['GET'], requirements: ['serviceId' => '\\d+'])]
    public function transfers(int $serviceId): Response
    {
        return $this->render('nursing/request/transfers.html.twig', [
            'service_id' => $serviceId,
            'requests' => $this->nursingTransferRepository->findPendingByDestinationService($serviceId),
        ]);
    }

    #[Route('/request/discharges/{serviceId}', name: 'request_discharges', methods: ['GET'], requirements: ['serviceId' => '\\d+'])]
    public function discharges(int $serviceId): Response
    {
        return $this->render('nursing/request/discharges.html.twig', [
            'service_id' => $serviceId,
            'requests' => $this->nursingDischargeRepository->findRecentByService($serviceId),
        ]);
    }

    #[Route('/request/admit/{admissionId}', name: 'request_admit', methods: ['POST'], requirements: ['admissionId' => '\\d+'])]
    public function admit(int $admissionId, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('nursing_request_admit', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $serviceId = (int) $request->request->get('serviceId', 0);
        $bedId = (int) $request->request->get('bedId', 0);

        $record = $this->entityManager->find(AdmissionRecord::class, $admissionId);
        $bed = $this->bedRepository->findOneActiveById($bedId);

        if ($record instanceof AdmissionRecord && $bed instanceof Bed && $bed->getStatus() === 'available') {
            $record->setBed($bed);
            $record->setStatus('admitted');
            $bed->setStatus('occupied');
            $bed->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }

        if ($serviceId > 0) {
            return $this->redirectToRoute('app_nursing_request_admissions', ['serviceId' => $serviceId]);
        }

        return $this->redirectToRoute('app_nursing_index');
    }
}
