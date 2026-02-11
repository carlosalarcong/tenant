<?php

namespace App\Controller\Maintainers\Basic;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\JobPosition;
use App\Form\Maintainers\Labor\JobPositionType;
use App\Repository\Tenant\JobPositionRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Job Position Controller
 * 
 * Gestiona el mantenedor de Cargos
 */
#[Route('/maintainers/basic/job-position')]
class JobPositionController extends AbstractMantenedorController
{
    public function __construct(
        private JobPositionRepository $repository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_job_position_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_job_position_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_job_position_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_job_position_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }

    #[Route('/export', name: 'app_maintainers_job_position_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'description', 'isActive'],
            headers: $this->translateColumns(['name', 'description', 'is_active']),
            filename: 'cargos_' . date('Y-m-d') . '.csv'
        );
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->repository->createQueryBuilder('jp')
            ->orderBy('jp.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'description' => $this->translator->trans('maintainers.columns.description', [], 'maintainers'),
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/basic/job_position/index.html.twig';
    }

    protected function getFormType(): string
    {
        return JobPositionType::class;
    }

    protected function createNewEntity(): object
    {
        return new JobPosition();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_job_position_index';
    }

    protected function findEntity(int $id): ?object
    {
        return $this->repository->find($id);
    }
}
