<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\SurgeryItem;
use App\Form\Maintainers\Commercial\SurgeryItemType;
use App\Repository\Tenant\SurgeryItemRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/surgery-item')]
class SurgeryItemController extends AbstractMantenedorController
{
    public function __construct(
        private SurgeryItemRepository $surgeryItemRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->surgeryItemRepository->createQueryBuilder('si')
            ->orderBy('si.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'id' => $this->translator->trans('maintainers.columns.id', [], 'maintainers'),
        'code' => $this->translator->trans('maintainers.columns.code', [], 'maintainers'),
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'category' => 'Category',
        'unitCost' => 'UnitCost',
        'isSterile' => 'IsSterile',
        'isDisposable' => 'IsDisposable',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/surgery_item/index.html.twig';
    }

    protected function getFormType(): string
    {
        return SurgeryItemType::class;
    }

    protected function createNewEntity(): object
    {
        return new SurgeryItem();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_surgery_item_index';
    }

    #[Route('', name: 'app_maintainers_commercial_surgery_item_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_surgery_item_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_surgery_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SurgeryItem $surgeryItem): Response
    {
        return $this->handleEdit($request, $surgeryItem->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_surgery_item_delete', methods: ['DELETE'])]
    public function delete(Request $request, SurgeryItem $surgeryItem): Response
    {
        return $this->handleDelete($request, $surgeryItem->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_surgery_item_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
