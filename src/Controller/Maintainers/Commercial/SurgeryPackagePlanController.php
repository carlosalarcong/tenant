<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\Member;
use App\Entity\Tenant\SurgeryPackagePlan;
use App\Form\Maintainers\Commercial\SurgeryPackagePlanType;
use App\Repository\Tenant\SurgeryPackagePlanRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/surgery-package-plan')]
class SurgeryPackagePlanController extends AbstractMantenedorController
{
    public function __construct(
        private SurgeryPackagePlanRepository $surgeryPackagePlanRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->surgeryPackagePlanRepository->createQueryBuilder('spp')
            ->leftJoin('spp.branchPayer', 'bp')
            ->addSelect('bp')
            ->leftJoin('bp.branch', 'b')
            ->addSelect('b')
            ->leftJoin('bp.payer', 'p')
            ->addSelect('p')
            ->leftJoin('spp.createdBy', 'cb')
            ->addSelect('cb')
            ->orderBy('b.name', 'ASC')
            ->addOrderBy('spp.name', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Plan',
            'branchPayer.branch.name' => 'Sucursal',
            'branchPayer.payer.name' => 'Financiador',
            'createdAt' => 'Creado',
            'isActive' => 'Activo',
        ];
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/surgery_package_plan/index.html.twig';
    }

    protected function getFormType(): string
    {
        return SurgeryPackagePlanType::class;
    }

    protected function createNewEntity(): object
    {
        return new SurgeryPackagePlan();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_surgery_package_plan_index';
    }

    protected function beforeSave(object $entity, Request $request): void
    {
        if ($entity instanceof SurgeryPackagePlan && $entity->getCreatedBy() === null) {
            $user = $this->getUser();
            if ($user instanceof Member) {
                $entity->setCreatedBy($user);
            }
        }
    }

    #[Route('', name: 'app_maintainers_commercial_surgery_package_plan_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_surgery_package_plan_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_surgery_package_plan_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SurgeryPackagePlan $surgeryPackagePlan): Response
    {
        return $this->handleEdit($request, $surgeryPackagePlan->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_surgery_package_plan_delete', methods: ['DELETE'])]
    public function delete(Request $request, SurgeryPackagePlan $surgeryPackagePlan): Response
    {
        return $this->handleDelete($request, $surgeryPackagePlan->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_surgery_package_plan_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
