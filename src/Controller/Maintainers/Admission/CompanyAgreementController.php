<?php

namespace App\Controller\Maintainers\Admission;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\CompanyAgreement;
use App\Form\Maintainers\Admission\CompanyAgreementType;
use App\Repository\Tenant\CompanyAgreementRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CompanyAgreement Controller
 * 
 * Gestiona el mantenedor de Convenios de Empresa
 */
#[Route('/maintainers/admission/company-agreement')]
class CompanyAgreementController extends AbstractMantenedorController
{
    public function __construct(
        private CompanyAgreementRepository $companyAgreementRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_admission_company_agreement_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_admission_company_agreement_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_admission_company_agreement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_admission_company_agreement_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_admission_company_agreement_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'description', 'isActive'],
            headers: $this->translateColumns(['name', 'description', 'is_active']),
            filename: 'convenios_empresa_' . date('Y-m-d') . '.csv'
        );
    }

    // ========================================================================
    // Implementación de métodos abstractos
    // ========================================================================

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->companyAgreementRepository->createQueryBuilder('ca')
            ->orderBy('ca.id', 'DESC');
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
        return 'maintainers/admission/company_agreement/index.html.twig';
    }

    protected function getFormType(): string
    {
        return CompanyAgreementType::class;
    }

    protected function createNewEntity(): object
    {
        return new CompanyAgreement();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_admission_company_agreement_index';
    }

    protected function getPageTitle(?string $action = null): string
    {
        return match($action) {
            'create' => 'Crear Convenio Empresa',
            'edit' => 'Editar Convenio Empresa',
            default => 'Convenios de Empresa'
        };
    }

    // ========================================================================
    // Hooks personalizados (opcional)
    // ========================================================================

    protected function beforeSave(object $entity, Request $request): void
    {
        if ($entity instanceof CompanyAgreement) {
            $entity->setUpdatedAt(new \DateTime());
        }
    }

    protected function canDelete(object $entity): bool
    {
        return true;
    }
}
