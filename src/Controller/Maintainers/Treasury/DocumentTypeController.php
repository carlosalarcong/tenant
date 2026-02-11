<?php

namespace App\Controller\Maintainers\Treasury;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\DocumentType;
use App\Form\Maintainers\Treasury\DocumentTypeType;
use App\Repository\Tenant\DocumentTypeRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/treasury/document-type')]
class DocumentTypeController extends AbstractMantenedorController
{
    public function __construct(
        private readonly DocumentTypeRepository $repository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('/', name: 'app_maintainers_treasury_document_type_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_treasury_document_type_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_treasury_document_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_treasury_document_type_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }

    #[Route('/export', name: 'app_maintainers_treasury_document_type_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        $exportColumns = $this->getExportColumns();
        return $this->handleExport(
            request: $request,
            columns: array_keys($exportColumns),
            headers: array_values($exportColumns),
            filename: $this->getExportFileName()
        );
    }

    protected function getEntityClass(): string
    {
        return DocumentType::class;
    }

    protected function getFormType(): string
    {
        return DocumentTypeType::class;
    }

    protected function getRepository()
    {
        return $this->repository;
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/treasury/document_type/index.html.twig';
    }

    protected function getRoutePrefix(): string
    {
        return 'app_maintainers_treasury_document_type';
    }

    protected function getColumns(): array
    {
        return [
        'id' => $this->translator->trans('maintainers.columns.id', [], 'maintainers'),
        'siiCode' => 'Código SII',
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'isDte' => 'DTE',
        'isLogistics' => 'Logística',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function createNewEntity(): object
    {
        return new DocumentType();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_treasury_document_type_index';
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->repository->createQueryBuilder('dt')
            ->orderBy('dt.name', 'ASC');
    }

    protected function getExportColumns(): array
    {
        return [
            'id' => 'ID',
            'siiCode' => 'Código SII',
            'name' => 'Nombre',
            'isDte' => '¿Es DTE?',
            'isLogistics' => '¿Es de Logística?',
            'isActive' => 'Activo'
        ];
    }

    protected function getExportFileName(): string
    {
        return 'tipos_documento_' . date('Y-m-d_His') . '.csv';
    }
}
