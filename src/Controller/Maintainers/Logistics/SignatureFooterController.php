<?php

namespace App\Controller\Maintainers\Logistics;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\SignatureFooter;
use App\Form\Maintainers\Logistics\SignatureFooterType;
use App\Repository\Tenant\SignatureFooterRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * SignatureFooter Controller
 * 
 * Gestiona el mantenedor de Pies de Firma
 */
#[Route('/maintainers/logistics/signature-footer')]
class SignatureFooterController extends AbstractMantenedorController
{
    public function __construct(
        private SignatureFooterRepository $signatureFooterRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_logistics_signature_footer_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_logistics_signature_footer_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_logistics_signature_footer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_logistics_signature_footer_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_logistics_signature_footer_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['code', 'name', 'position', 'branch.name', 'isActive'],
            headers: $this->translateColumns(['code', 'name', 'position', 'branch', 'is_active']),
            filename: 'pies_firma_' . date('Y-m-d') . '.csv'
        );
    }

    // ========================================================================
    // Implementación de métodos abstractos
    // ========================================================================

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->signatureFooterRepository->createQueryBuilder('sf')
            ->leftJoin('sf.branch', 'b')
            ->orderBy('sf.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'code' => $this->translator->trans('maintainers.columns.code', [], 'maintainers'),
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'position' => 'Cargo',
        'branch.name' => 'Sucursal',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/logistics/signature_footer/index.html.twig';
    }

    protected function getFormType(): string
    {
        return SignatureFooterType::class;
    }

    protected function createNewEntity(): object
    {
        return new SignatureFooter();
    }

    protected function getEntityById(int $id): ?object
    {
        return $this->signatureFooterRepository->find($id);
    }

    protected function getDeleteSuccessMessage(): string
    {
        return 'Pie de Firma eliminado exitosamente';
    }

    protected function getDeleteErrorMessage(): string
    {
        return 'No se pudo eliminar el Pie de Firma';
    }

    protected function getEntityNotFoundMessage(): string
    {
        return 'Pie de Firma no encontrado';
    }

    protected function getCreateSuccessMessage(): string
    {
        return 'Pie de Firma creado exitosamente';
    }

    protected function getUpdateSuccessMessage(): string
    {
        return 'Pie de Firma actualizado exitosamente';
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_logistics_signature_footer_index';
    }

    protected function getEditRoute(): string
    {
        return 'app_maintainers_logistics_signature_footer_edit';
    }

    protected function getCreateRoute(): string
    {
        return 'app_maintainers_logistics_signature_footer_create';
    }

}
