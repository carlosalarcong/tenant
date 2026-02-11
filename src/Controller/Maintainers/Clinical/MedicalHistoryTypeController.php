<?php
namespace App\Controller\Maintainers\Clinical;
use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\MedicalHistoryType;
use App\Form\Maintainers\Clinical\MedicalHistoryTypeForm;
use App\Repository\Tenant\MedicalHistoryTypeRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
#[Route('/maintainers/clinical/medical-history-type')]
class MedicalHistoryTypeController extends AbstractMantenedorController
{
    public function __construct(private MedicalHistoryTypeRepository $repository, TenantEntityManager $tem, ExportService $es, TranslatorInterface $translator) {
        parent::__construct($tem, $translator);
        $this->setExportService($es);
    }
    #[Route('', name: 'app_maintainers_clinical_medical_history_type_index', methods: ['GET'])]
    public function index(Request $request): Response { return $this->handleIndex($request); }
    #[Route('/create', name: 'app_maintainers_clinical_medical_history_type_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response { return $this->handleCreate($request); }
    #[Route('/{id}/edit', name: 'app_maintainers_clinical_medical_history_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response { return $this->handleEdit($request, $id); }
    #[Route('/{id}/delete', name: 'app_maintainers_clinical_medical_history_type_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response { return $this->handleDelete($request, $id); }
    #[Route('/export', name: 'app_maintainers_clinical_medical_history_type_export', methods: ['GET'])]
    public function export(Request $request): Response {
        return $this->handleExport(request: $request, columns: ['name', 'isActive'], headers: $this->translateColumns(['name', 'is_active']), filename: 'tipos_antecedentes_' . date('Y-m-d') . '.csv');
    }
    protected function getData(Request $request): array|QueryBuilder { return $this->repository->createQueryBuilder('mht')->orderBy('mht.id', 'DESC'); }
    protected function getColumns(): array { return ['name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'), 'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')]; }
    protected function getTemplatePath(): string { return 'maintainers/clinical/medical_history_type/index.html.twig'; }
    protected function getFormType(): string { return MedicalHistoryTypeForm::class; }
    protected function createNewEntity(): object { return new MedicalHistoryType(); }
    protected function getIndexRoute(): string { return 'app_maintainers_clinical_medical_history_type_index'; }
    protected function getPageTitle(?string $action = null): string { 
        return match($action) { 
            'create' => $this->translator->trans('maintainers.titles.create_medical_history_type', [], 'maintainers'), 
            'edit' => $this->translator->trans('maintainers.titles.edit_medical_history_type', [], 'maintainers'), 
            default => $this->translator->trans('maintainers.titles.medical_history_type_list', [], 'maintainers') 
        }; 
    }
    protected function beforeSave(object $entity, Request $request): void { if ($entity instanceof MedicalHistoryType) { $entity->setUpdatedAt(new \DateTime()); } }
    protected function canDelete(object $entity): bool { return true; }
}
