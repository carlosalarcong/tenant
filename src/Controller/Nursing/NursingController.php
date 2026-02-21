<?php

namespace App\Controller\Nursing;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\Bed;
use App\Entity\Tenant\Service;
use App\Repository\Tenant\AdmissionRecordRepository;
use App\Repository\Tenant\BedRepository;
use App\Repository\Tenant\NursingDischargeRepository;
use App\Repository\Tenant\NursingTransferRepository;
use App\Repository\Tenant\ServiceRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nursing', name: 'app_nursing_')]
class NursingController extends AbstractTenantAwareController
{
    public function __construct(
        private TenantEntityManager $entityManager,
        private ServiceRepository $serviceRepository,
        private BedRepository $bedRepository,
        private AdmissionRecordRepository $admissionRecordRepository,
        private NursingTransferRepository $nursingTransferRepository,
        private NursingDischargeRepository $nursingDischargeRepository
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $services = $this->serviceRepository->findAllActive();
        $selectedServiceId = $this->parseServiceId((string) $request->query->get('serviceId', ''));
        $selectedService = null;
        $roomsWithBeds = [];
        $admissionsByBed = [];
        $pendingRequests = 0;
        $managementStats = [];
        $legendCounts = [];
        $virtualBedPatients = [];

        if (null !== $selectedServiceId) {
            $selectedService = $this->serviceRepository->findOneActiveById($selectedServiceId);

            if ($selectedService instanceof Service) {
                [
                    'rooms_with_beds' => $roomsWithBeds,
                    'admissions_by_bed' => $admissionsByBed,
                    'pending_requests' => $pendingRequests,
                    'management_stats' => $managementStats,
                    'legend_counts' => $legendCounts,
                    'virtual_bed_patients' => $virtualBedPatients,
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
            'management_stats' => $managementStats,
            'legend_counts' => $legendCounts,
            'virtual_bed_patients' => $virtualBedPatients,
        ]);
    }

    #[Route('/service/{serviceId}/board', name: 'service_board', methods: ['GET'], requirements: ['serviceId' => '\d+'])]
    public function board(int $serviceId): Response
    {
        $service = $this->serviceRepository->findOneActiveById($serviceId);
        if (!$service instanceof Service) {
            throw $this->createNotFoundException('Servicio no encontrado.');
        }

        [
            'rooms_with_beds' => $roomsWithBeds,
            'admissions_by_bed' => $admissionsByBed,
            'pending_requests' => $pendingRequests,
            'management_stats' => $managementStats,
            'legend_counts' => $legendCounts,
            'virtual_bed_patients' => $virtualBedPatients,
        ] = $this->buildBoardData($serviceId);

        return $this->render('nursing/service/_board.html.twig', [
            'service' => $service,
            'rooms_with_beds' => $roomsWithBeds,
            'admissions_by_bed' => $admissionsByBed,
            'pending_requests' => $pendingRequests,
            'management_stats' => $managementStats,
            'legend_counts' => $legendCounts,
            'virtual_bed_patients' => $virtualBedPatients,
        ]);
    }

    #[Route('/service/{serviceId}/board/list', name: 'service_board_list', methods: ['GET'], requirements: ['serviceId' => '\d+'])]
    public function boardList(int $serviceId): Response
    {
        $service = $this->serviceRepository->findOneActiveById($serviceId);
        if (!$service instanceof Service) {
            throw $this->createNotFoundException('Servicio no encontrado.');
        }

        [
            'rooms_with_beds' => $roomsWithBeds,
            'admissions_by_bed' => $admissionsByBed,
            'pending_requests' => $pendingRequests,
            'management_stats' => $managementStats,
            'legend_counts' => $legendCounts,
            'virtual_bed_patients' => $virtualBedPatients,
        ] = $this->buildBoardData($serviceId);

        return $this->render('nursing/service/_board_list.html.twig', [
            'service' => $service,
            'rooms_with_beds' => $roomsWithBeds,
            'admissions_by_bed' => $admissionsByBed,
            'pending_requests' => $pendingRequests,
            'management_stats' => $managementStats,
            'legend_counts' => $legendCounts,
            'virtual_bed_patients' => $virtualBedPatients,
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
            if (!$service instanceof Service || null === $service->getId()) {
                continue;
            }

            $summary[$service->getId()] = $this->bedRepository->countAvailableBedsForService($service->getId());
        }

        return $summary;
    }

    private function buildBoardData(int $serviceId): array
    {
        $beds = $this->bedRepository->findActiveBedsForService($serviceId);
        $admissions = $this->admissionRecordRepository->findActiveByService($serviceId);
        $pendingRequests = $this->admissionRecordRepository->countPendingRequestsByService($serviceId);
        $pendingIncomingTransfers = $this->nursingTransferRepository->countPendingByDestinationService($serviceId);
        $pendingOutgoingTransfers = $this->nursingTransferRepository->countPendingByOriginService($serviceId);
        $dischargeRequests = $this->nursingDischargeRepository->countByServiceAndType($serviceId, 'alta');
        $virtualBedPatients = $this->nursingTransferRepository->findPendingByDestinationService($serviceId);

        $admissionsByBed = [];
        foreach ($admissions as $admission) {
            $bed = $admission->getBed();
            if ($bed instanceof Bed && null !== $bed->getId()) {
                $admissionsByBed[$bed->getId()] = $admission;
            }
        }

        $legendCounts = [
            'available' => 0,
            'occupied' => 0,
            'blocked' => 0,
            'reserved' => 0,
        ];

        $roomsWithBeds = [];
        foreach ($beds as $bed) {
            if (!$bed instanceof Bed || null === $bed->getId()) {
                continue;
            }

            $status = strtolower((string) ($bed->getStatus() ?? 'reserved'));
            if ('occupied' === $status) {
                $legendCounts['occupied']++;
            } elseif ('available' === $status) {
                $legendCounts['available']++;
            } elseif ('maintenance' === $status) {
                $legendCounts['blocked']++;
            } elseif (in_array($status, ['reserved', 'cleaning'], true)) {
                $legendCounts['reserved']++;
            } else {
                $legendCounts['blocked']++;
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
            'management_stats' => [
                'incoming_admissions' => $pendingRequests,
                'incoming_transfers' => $pendingIncomingTransfers,
                'outgoing_transfers' => $pendingOutgoingTransfers,
                'discharge_requests' => $dischargeRequests,
            ],
            'legend_counts' => $legendCounts,
            'virtual_bed_patients' => $virtualBedPatients,
        ];
    }
}
