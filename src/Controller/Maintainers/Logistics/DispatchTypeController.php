<?php

namespace App\Controller\Maintainers\Logistics;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\DispatchType;
use App\Form\Maintainers\Logistics\DispatchTypeType;
use App\Repository\Tenant\DispatchTypeRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * DispatchType Controller
 * 
 * Gestiona el mantenedor de Tipos de Despacho
 */
#[Route('/maintainers/logistics/dispatch-type')]
class DispatchTypeController extends AbstractMantenedorController
{
    public function __construct(
        private DispatchTypeRepository $dispatchTypeRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_logistics_dispatch_type_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_logistics_dispatch_type_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_logistics_dispatch_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_logistics_dispatch_type_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_logistics_dispatch_type_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['code', 'name', 'isActive'],
            headers: $this->translateColumns(['code', 'name', 'is_active']),
            filename: 'tipos_despacho_' . date('Y-m-d') . '.csv'
        );
    }

    // ========================================================================
    // Implementación de métodos abstractos
    // ========================================================================

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->dispatchTypeRepository->createQueryBuilder('dt')
            ->orderBy('dt.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'code' => $this->translator->trans('maintainers.columns.code', [], 'maintainers'),
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/logistics/dispatch_type/index.html.twig';
    }

    protected function getFormType(): string
    {
        return DispatchTypeType::class;
    }

    protected function createNewEntity(): object
    {
        return new DispatchType();
    }

    protected function getEntityById(int $id): ?object
    {
        return $this->dispatchTypeRepository->find($id);
    }

    protected function getDeleteSuccessMessage(): string
    {
        return 'Tipo de Despacho eliminado exitosamente';
    }

    protected function getDeleteErrorMessage(): string
    {
        return 'No se pudo eliminar el Tipo de Despacho';
    }

    protected function getEntityNotFoundMessage(): string
    {
        return 'Tipo de Despacho no encontrado';
    }

    protected function getCreateSuccessMessage(): string
    {
        return 'Tipo de Despacho creado exitosamente';
    }

    protected function getUpdateSuccessMessage(): string
    {
        return 'Tipo de Despacho actualizado exitosamente';
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_logistics_dispatch_type_index';
    }

    protected function getEditRoute(): string
    {
        return 'app_maintainers_logistics_dispatch_type_edit';
    }

    protected function getCreateRoute(): string
    {
        return 'app_maintainers_logistics_dispatch_type_create';
    }

}
