<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\RequestingCompany;
use App\Form\Maintainers\Commercial\RequestingCompanyType;
use App\Repository\Tenant\RequestingCompanyRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/requesting-company')]
class RequestingCompanyController extends AbstractMantenedorController
{
    public function __construct(
        private RequestingCompanyRepository $requestingCompanyRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->requestingCompanyRepository->createQueryBuilder('rc')
            ->orderBy('rc.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'id' => $this->translator->trans('maintainers.columns.id', [], 'maintainers'),
        'code' => $this->translator->trans('maintainers.columns.code', [], 'maintainers'),
        'businessName' => 'BusinessName',
        'rut' => 'RUT',
        'phone' => 'Phone',
        'hasAgreement' => 'HasAgreement',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/requesting_company/index.html.twig';
    }

    protected function getFormType(): string
    {
        return RequestingCompanyType::class;
    }

    protected function createNewEntity(): object
    {
        return new RequestingCompany();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_requesting_company_index';
    }

    #[Route('', name: 'app_maintainers_commercial_requesting_company_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_requesting_company_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_requesting_company_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RequestingCompany $requestingCompany): Response
    {
        return $this->handleEdit($request, $requestingCompany->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_requesting_company_delete', methods: ['DELETE'])]
    public function delete(Request $request, RequestingCompany $requestingCompany): Response
    {
        return $this->handleDelete($request, $requestingCompany->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_requesting_company_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
