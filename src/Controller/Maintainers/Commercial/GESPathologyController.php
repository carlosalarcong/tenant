<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\GESPathology;
use App\Form\Maintainers\Commercial\GESPathologyType;
use App\Repository\Tenant\GESPathologyRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/ges-pathology')]
class GESPathologyController extends AbstractMantenedorController
{
    public function __construct(
        private GESPathologyRepository $gesPathologyRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->gesPathologyRepository->createQueryBuilder('gp')
            ->orderBy('gp.pathologyNumber', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
        'id' => $this->translator->trans('maintainers.columns.id', [], 'maintainers'),
        'pathologyNumber' => 'PathologyNumber',
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'minAge' => 'MinAge',
        'maxAge' => 'MaxAge',
        'genderRestriction' => 'GenderRestriction',
        'guaranteedDays' => 'GuaranteedDays',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/ges_pathology/index.html.twig';
    }

    protected function getFormType(): string
    {
        return GESPathologyType::class;
    }

    protected function createNewEntity(): object
    {
        return new GESPathology();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_ges_pathology_index';
    }

    #[Route('', name: 'app_maintainers_commercial_ges_pathology_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_ges_pathology_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_ges_pathology_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, GESPathology $gesPathology): Response
    {
        return $this->handleEdit($request, $gesPathology->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_ges_pathology_delete', methods: ['DELETE'])]
    public function delete(Request $request, GESPathology $gesPathology): Response
    {
        return $this->handleDelete($request, $gesPathology->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_ges_pathology_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
