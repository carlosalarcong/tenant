<?php

namespace App\Controller\Maintainers\Hospital;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\PrescriptionRuleDetail;
use App\Form\Maintainers\Hospital\PrescriptionRuleDetailType;
use App\Repository\Tenant\PrescriptionRuleDetailRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/hospital/prescription-rule-detail')]
class PrescriptionRuleDetailController extends AbstractMantenedorController
{
    public function __construct(
        private PrescriptionRuleDetailRepository $prescriptionRuleDetailRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_hospital_prescription_rule_detail_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_hospital_prescription_rule_detail_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_hospital_prescription_rule_detail_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_hospital_prescription_rule_detail_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_hospital_prescription_rule_detail_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['intervals', 'dailyQuantity', 'isActive'],
            headers: $this->translateColumns(['intervals', 'daily_quantity', 'is_active']),
            filename: 'reglas_prescripcion_' . date('Y-m-d') . '.csv'
        );
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->prescriptionRuleDetailRepository->createQueryBuilder('prd')
            ->orderBy('prd.dailyQuantity', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
        'intervals' => 'Intervalos',
        'dailyQuantity' => 'Cant/Dia',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/hospital/prescription_rule_detail/index.html.twig';
    }

    protected function getFormType(): string
    {
        return PrescriptionRuleDetailType::class;
    }

    protected function createNewEntity(): object
    {
        return new PrescriptionRuleDetail();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_hospital_prescription_rule_detail_index';
    }

    protected function beforeSave(object $entity, Request $request): void
    {
        $entity->setUpdatedAt(new \DateTime());
    }

    protected function canDelete(object $entity): bool
    {
        return true;
    }

}
