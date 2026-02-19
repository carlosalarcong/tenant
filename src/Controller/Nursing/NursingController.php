<?php

namespace App\Controller\Nursing;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\Bed;
use App\Entity\Tenant\MedicalService;
use App\Repository\Tenant\AdmissionRecordRepository;
use App\Repository\Tenant\BedRepository;
use App\Repository\Tenant\MedicalServiceRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nursing', name: 'app_nursing_')]
class NursingController extends AbstractTenantAwareController
{
    public function __construct(
        private TenantEntityManager $entityManager,
        private MedicalServiceRepository $medicalServiceRepository,
        private BedRepository $bedRepository,
        private AdmissionRecordRepository $admissionRecordRepository
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $services = $this->medicalServiceRepository->findAllActive();
        $selectedServiceId = $this->parseServiceId((string) $request->query->get('serviceId', ''));
        $selectedService = null;
        $roomsWithBeds = [];
        $admissionsByBed = [];
        $pendingRequests = 0;

        if (null !== $selectedServiceId) {
            $selectedService = $this->medicalServiceRepository->findOneActiveById($selectedServiceId);

            if ($selectedService instanceof MedicalService) {
                [
                    'rooms_with_beds' => $roomsWithBeds,
                    'admissions_by_bed' => $admissionsByBed,
                    'pending_requests' => $pendingRequests,
                ] = $this->buildBoardData($selectedServiceId);
            }
        }

        return $this->render('nursing/index.html.twig', [
            'services' => $services,
            'selected_service_id' => $selectedService?->getId(),
            'selected_service' => $selectedService,
            'available_beds_by_service' => $this->buildAvailableBedsSummary($services),
            'rooms_with_beds' => $roomsWithBeds,
            'admissions_by_bed' => $admissionsByBed,
            'pending_requests' => $pendingRequests,
        ]);
    }

    #[Route('/service/{serviceId}/board', name: 'service_board', methods: ['GET'], requirements: ['serviceId' => '\d+'])]
    public function board(int $serviceId): Response
    {
        $service = $this->medicalServiceRepository->findOneActiveById($serviceId);
        if (!$service instanceof MedicalService) {
            throw $this->createNotFoundException('Servicio no encontrado.');
        }

        [
            'rooms_with_beds' => $roomsWithBeds,
            'admissions_by_bed' => $admissionsByBed,
            'pending_requests' => $pendingRequests,
        ] = $this->buildBoardData($serviceId);

        return $this->render('nursing/service/_board.html.twig', [
            'service' => $service,
            'rooms_with_beds' => $roomsWithBeds,
            'admissions_by_bed' => $admissionsByBed,
            'pending_requests' => $pendingRequests,
        ]);
    }

    #[Route('/service/{serviceId}/board/list', name: 'service_board_list', methods: ['GET'], requirements: ['serviceId' => '\d+'])]
    public function boardList(int $serviceId): Response
    {
        $service = $this->medicalServiceRepository->findOneActiveById($serviceId);
        if (!$service instanceof MedicalService) {
            throw $this->createNotFoundException('Servicio no encontrado.');
        }

        [
            'rooms_with_beds' => $roomsWithBeds,
            'admissions_by_bed' => $admissionsByBed,
            'pending_requests' => $pendingRequests,
        ] = $this->buildBoardData($serviceId);

        return $this->render('nursing/service/_board_list.html.twig', [
            'service' => $service,
            'rooms_with_beds' => $roomsWithBeds,
            'admissions_by_bed' => $admissionsByBed,
            'pending_requests' => $pendingRequests,
        ]);
    }

    private function parseServiceId(string $rawServiceId): ?int
    {
        if (!ctype_digit($rawServiceId)) {
            return null;
        }

        return (int) $rawServiceId;
    }

    private function buildAvailableBedsSummary(array $services): array
    {
        $summary = [];
        foreach ($services as $service) {
            if (!$service instanceof MedicalService || null === $service->getId()) {
                continue;
            }

            $summary[$service->getId()] = $this->bedRepository->countAvailableBedsForMedicalService($service->getId());
        }

        return $summary;
    }

    private function buildBoardData(int $serviceId): array
    {
        $beds = $this->bedRepository->findActiveBedsForMedicalService($serviceId);
        $admissions = $this->admissionRecordRepository->findActiveByMedicalService($serviceId);
        $pendingRequests = $this->admissionRecordRepository->countPendingRequestsByMedicalService($serviceId);

        $admissionsByBed = [];
        foreach ($admissions as $admission) {
            $bed = $admission->getBed();
            if ($bed instanceof Bed && null !== $bed->getId()) {
                $admissionsByBed[$bed->getId()] = $admission;
            }
        }

        $roomsWithBeds = [];
        foreach ($beds as $bed) {
            if (!$bed instanceof Bed || null === $bed->getId()) {
                continue;
            }

            $room = $bed->getRoom();
            $roomId = $room?->getId() ?? 0;

            if (!isset($roomsWithBeds[$roomId])) {
                $roomsWithBeds[$roomId] = [
                    'room' => $room,
                    'beds' => [],
                ];
            }

            $roomsWithBeds[$roomId]['beds'][] = $bed;
        }

        return [
            'rooms_with_beds' => array_values($roomsWithBeds),
            'admissions_by_bed' => $admissionsByBed,
            'pending_requests' => $pendingRequests,
        ];
    }
}
