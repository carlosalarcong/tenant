<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\Room;
use App\Form\Maintainers\Commercial\RoomType;
use App\Repository\Tenant\RoomRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/room')]
class RoomController extends AbstractMantenedorController
{
    public function __construct(
        private RoomRepository $roomRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->roomRepository->createQueryBuilder('r')
            ->leftJoin('r.clinic', 'c')
            ->addSelect('c')
            ->orderBy('r.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'id' => $this->translator->trans('maintainers.columns.id', [], 'maintainers'),
        'roomNumber' => 'RoomNumber',
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'roomType' => 'RoomType',
        'floor' => 'Floor',
        'capacity' => 'Capacity',
        'status' => 'Status',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/room/index.html.twig';
    }

    protected function getFormType(): string
    {
        return RoomType::class;
    }

    protected function createNewEntity(): object
    {
        return new Room();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_room_index';
    }

    #[Route('', name: 'app_maintainers_commercial_room_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_room_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_room_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Room $room): Response
    {
        return $this->handleEdit($request, $room->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_room_delete', methods: ['DELETE'])]
    public function delete(Request $request, Room $room): Response
    {
        return $this->handleDelete($request, $room->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_room_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
