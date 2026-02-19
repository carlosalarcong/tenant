<?php

namespace App\Controller\Nursing;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\Bed;
use App\Form\Nursing\NursingBedStatusType;
use App\Repository\Tenant\AdmissionRecordRepository;
use App\Repository\Tenant\BedRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nursing', name: 'app_nursing_')]
class NursingBedController extends AbstractTenantAwareController
{
    public function __construct(
        private TenantEntityManager $entityManager,
        private BedRepository $bedRepository,
        private AdmissionRecordRepository $admissionRecordRepository
    ) {}

    #[Route('/bed/{id}/status', name: 'bed_status', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function status(int $id, Request $request): Response
    {
        $serviceId = $this->parseServiceId((string) $request->query->get('serviceId', (string) $request->request->get('serviceId', '')));
        $bed = $this->bedRepository->findOneActiveById($id);
        if (!$bed instanceof Bed) {
            throw $this->createNotFoundException('Cama no encontrada.');
        }

        $form = $this->createForm(NursingBedStatusType::class, $bed, [
            'action' => $this->generateUrl('app_nursing_bed_status', ['id' => $id, 'serviceId' => $serviceId]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($bed->getStatus() === 'maintenance') {
                $bed->setLastMaintenanceDate(new \DateTimeImmutable());
            }
            $bed->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            if (null !== $serviceId) {
                return $this->redirectToRoute('app_nursing_service_board', ['serviceId' => $serviceId]);
            }
        }

        return $this->render('nursing/bed/_status_form.html.twig', [
            'bed' => $bed,
            'service_id' => $serviceId,
            'status_form' => $form->createView(),
        ]);
    }

    #[Route('/bed/{id}/assign', name: 'bed_assign', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function assign(int $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('nursing_bed_assign', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $serviceId = $this->parseServiceId((string) $request->request->get('serviceId', ''));
        $admissionRecordId = $this->parseServiceId((string) $request->request->get('admissionRecordId', ''));

        $bed = $this->bedRepository->findOneActiveById($id);
        if (!$bed instanceof Bed) {
            throw $this->createNotFoundException('Cama no encontrada.');
        }

        if (null === $serviceId || null === $admissionRecordId) {
            return $this->redirectToBoard($serviceId);
        }

        if ($bed->getStatus() !== 'available') {
            return $this->redirectToBoard($serviceId);
        }

        $admissionRecord = $this->admissionRecordRepository->findPendingByIdAndMedicalService($admissionRecordId, $serviceId);
        if (null === $admissionRecord) {
            return $this->redirectToBoard($serviceId);
        }

        $admissionRecord->setBed($bed);
        $admissionRecord->setStatus('admitted');
        $bed->setStatus('occupied');
        $bed->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $this->redirectToBoard($serviceId);
    }

    private function parseServiceId(string $rawValue): ?int
    {
        if (!ctype_digit($rawValue)) {
            return null;
        }

        return (int) $rawValue;
    }

    private function redirectToBoard(?int $serviceId): RedirectResponse
    {
        if (null === $serviceId) {
            return $this->redirectToRoute('app_nursing_index');
        }

        return $this->redirectToRoute('app_nursing_service_board', ['serviceId' => $serviceId]);
    }
}
