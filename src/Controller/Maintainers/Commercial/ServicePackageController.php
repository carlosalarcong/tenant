<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\ServicePackage;
use App\Form\Maintainers\Commercial\ServicePackageType;
use App\Repository\Tenant\ServicePackageRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/service-package')]
class ServicePackageController extends AbstractMantenedorController
{
    public function __construct(
        private ServicePackageRepository $servicePackageRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->servicePackageRepository->createQueryBuilder('sp')
            ->orderBy('sp.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'id' => $this->translator->trans('maintainers.columns.id', [], 'maintainers'),
        'code' => $this->translator->trans('maintainers.columns.code', [], 'maintainers'),
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'isBillable' => 'IsBillable',
        'isProgram' => 'IsProgram',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/service_package/index.html.twig';
    }

    protected function getFormType(): string
    {
        return ServicePackageType::class;
    }

    protected function createNewEntity(): object
    {
        return new ServicePackage();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_service_package_index';
    }

    #[Route('', name: 'app_maintainers_commercial_service_package_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_service_package_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_service_package_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ServicePackage $servicePackage): Response
    {
        return $this->handleEdit($request, $servicePackage->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_service_package_delete', methods: ['DELETE'])]
    public function delete(Request $request, ServicePackage $servicePackage): Response
    {
        return $this->handleDelete($request, $servicePackage->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_service_package_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
