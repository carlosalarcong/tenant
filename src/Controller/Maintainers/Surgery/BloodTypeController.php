<?php

namespace App\Controller\Maintainers\Surgery;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\BloodType;
use App\Form\Maintainers\Surgery\BloodTypeType;
use App\Repository\Tenant\BloodTypeRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/surgery/blood-type')]
class BloodTypeController extends AbstractMantenedorController
{
    public function __construct(
        private readonly BloodTypeRepository $repository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_surgery_blood_type_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_surgery_blood_type_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_surgery_blood_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_surgery_blood_type_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }

    #[Route('/export', name: 'app_maintainers_surgery_blood_type_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_surgery_blood_type_index';
    }

    protected function getEntityClass(): string
    {
        return BloodType::class;
    }

    protected function getFormType(): string
    {
        return BloodTypeType::class;
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/surgery/blood_type/index.html.twig';
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->repository->createQueryBuilder('bt')
            ->orderBy('bt.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'description' => $this->translator->trans('maintainers.columns.description', [], 'maintainers'),
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function createNewEntity(): object
    {
        return new BloodType();
    }

    protected function getExportColumns(): array
    {
        return ['name', 'description', 'isActive'];
    }

    protected function getExportHeaders(): array
    {
        return ['Nombre', 'Descripción', 'Activo'];
    }

    protected function getExportFileName(): string
    {
        return 'grupos_sanguineos_' . date('Y-m-d') . '.csv';
    }
}
