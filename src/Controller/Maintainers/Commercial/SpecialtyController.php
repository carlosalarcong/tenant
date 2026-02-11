<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\Specialty;
use App\Form\Maintainers\Commercial\SpecialtyType;
use App\Repository\Tenant\SpecialtyRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/specialty')]
class SpecialtyController extends AbstractMantenedorController
{
    public function __construct(
        private SpecialtyRepository $specialtyRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->specialtyRepository->createQueryBuilder('s')
            ->orderBy('s.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'id' => $this->translator->trans('maintainers.columns.id', [], 'maintainers'),
        'code' => $this->translator->trans('maintainers.columns.code', [], 'maintainers'),
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'category' => 'Category',
        'defaultConsultationDuration' => 'DefaultConsultationDuration',
        'requiresCertification' => 'RequiresCertification',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/specialty/index.html.twig';
    }

    protected function getFormType(): string
    {
        return SpecialtyType::class;
    }

    protected function createNewEntity(): object
    {
        return new Specialty();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_specialty_index';
    }

    #[Route('', name: 'app_maintainers_commercial_specialty_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_specialty_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_specialty_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Specialty $specialty): Response
    {
        return $this->handleEdit($request, $specialty->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_specialty_delete', methods: ['DELETE'])]
    public function delete(Request $request, Specialty $specialty): Response
    {
        return $this->handleDelete($request, $specialty->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_specialty_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
