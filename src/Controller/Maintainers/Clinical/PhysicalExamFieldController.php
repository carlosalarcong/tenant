<?php
namespace App\Controller\Maintainers\Clinical;
use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\PhysicalExamField;
use App\Form\Maintainers\Clinical\PhysicalExamFieldType;
use App\Repository\Tenant\PhysicalExamFieldRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/clinical/physical-exam-field')]
class PhysicalExamFieldController extends AbstractMantenedorController
{
    public function __construct(
        private PhysicalExamFieldRepository $repository,
        TenantEntityManager $tem,
        ExportService $es,
        TranslatorInterface $translator
    ) {
        parent::__construct($tem, $translator);
        $this->setExportService($es);
    }

    #[Route('', name: 'app_maintainers_clinical_physical_exam_field_index', methods: ['GET'])]
    public function index(Request $request): Response {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_clinical_physical_exam_field_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_clinical_physical_exam_field_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_clinical_physical_exam_field_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response {
        return $this->handleDelete($request, $id);
    }

    #[Route('/export', name: 'app_maintainers_clinical_physical_exam_field_export', methods: ['GET'])]
    public function export(Request $request): Response {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'description', 'isActive'],
            headers: $this->translateColumns(['name', 'description', 'is_active']),
            filename: 'campos_examen_fisico_' . date('Y-m-d') . '.csv'
        );
    }

    protected function getData(Request $request): array|QueryBuilder {
        return $this->repository->createQueryBuilder('pef')
            ->orderBy('pef.id', 'DESC');
    }

    protected function getColumns(): array {
        return [
            'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
            'description' => $this->translator->trans('maintainers.columns.description', [], 'maintainers'),
            'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
        ];
    }

    protected function getTemplatePath(): string {
        return 'maintainers/clinical/physical_exam_field/index.html.twig';
    }

    protected function getFormType(): string {
        return PhysicalExamFieldType::class;
    }

    protected function createNewEntity(): object {
        return new PhysicalExamField();
    }

    protected function getIndexRoute(): string {
        return 'app_maintainers_clinical_physical_exam_field_index';
    }

    protected function getPageTitle(?string $action = null): string {
        return match($action) {
            'create' => $this->translator->trans('maintainers.titles.create_physical_exam_field', [], 'maintainers'),
            'edit' => $this->translator->trans('maintainers.titles.edit_physical_exam_field', [], 'maintainers'),
            default => $this->translator->trans('maintainers.titles.physical_exam_field_list', [], 'maintainers')
        };
    }

    protected function beforeSave(object $entity, Request $request): void {
        if ($entity instanceof PhysicalExamField) {
            $entity->setUpdatedAt(new \DateTime());
        }
    }

    protected function canDelete(object $entity): bool {
        return true;
    }
}
