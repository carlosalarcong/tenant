<?php

namespace App\Controller\Maintainers\Settlements;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\CompanyUserAssociation;
use App\Form\Maintainers\Settlements\CompanyUserAssociationType;
use App\Repository\Tenant\CompanyUserAssociationRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/settlements/company-user-association')]
class CompanyUserAssociationController extends AbstractMantenedorController
{
    public function __construct(
        private CompanyUserAssociationRepository $companyUserAssociationRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_settlements_company_user_association_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_settlements_company_user_association_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_settlements_company_user_association_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_settlements_company_user_association_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_settlements_company_user_association_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'isActive'],
            headers: $this->translateColumns(['name', 'is_active']),
            filename: 'asociaciones_empresa_usuario_' . date('Y-m-d') . '.csv'
        );
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->companyUserAssociationRepository->createQueryBuilder('cua')
            ->orderBy('cua.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
            'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
            'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
        ];
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/settlements/company_user_association/index.html.twig';
    }

    protected function getFormType(): string
    {
        return CompanyUserAssociationType::class;
    }

    protected function createNewEntity(): object
    {
        return new CompanyUserAssociation();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_settlements_company_user_association_index';
    }

    protected function getPageTitle(?string $action = null): string
    {
        return match($action) {
            'create' => 'Crear Asociacion Empresa Usuario',
            'edit' => 'Editar Asociacion Empresa Usuario',
            default => 'Asociaciones Empresa Usuario'
        };
    }

    protected function beforeSave(object $entity, Request $request): void
    {
        if ($entity instanceof CompanyUserAssociation) {
            $entity->setUpdatedAt(new \DateTime());
        }
    }

    protected function canDelete(object $entity): bool
    {
        return true;
    }
}
