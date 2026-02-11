<?php
namespace App\Controller\Maintainers\Clinical;
use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\ExamGroup;
use App\Form\Maintainers\Clinical\ExamGroupType;
use App\Repository\Tenant\ExamGroupRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/clinical/exam-group')]
class ExamGroupController extends AbstractMantenedorController
{
    public function __construct(
        private ExamGroupRepository $repository,
        TenantEntityManager $tem,
        ExportService $es,
        TranslatorInterface $translator
    ) {
        parent::__construct($tem, $translator);
        $this->setExportService($es);
    }

    #[Route('', name: 'app_maintainers_clinical_exam_group_index', methods: ['GET'])]
    public function index(Request $request): Response {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_clinical_exam_group_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_clinical_exam_group_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_clinical_exam_group_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response {
        return $this->handleDelete($request, $id);
    }

    #[Route('/export', name: 'app_maintainers_clinical_exam_group_export', methods: ['GET'])]
    public function export(Request $request): Response {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'isActive'],
            headers: $this->translateColumns(['name', 'is_active']),
            filename: 'agrupaciones_examen_' . date('Y-m-d') . '.csv'
        );
    }

    protected function getData(Request $request): array|QueryBuilder {
        return $this->repository->createQueryBuilder('eg')
            ->orderBy('eg.id', 'DESC');
    }

    protected function getColumns(): array {
        return [
            'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
            'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
        ];
    }

    protected function getTemplatePath(): string {
        return 'maintainers/clinical/exam_group/index.html.twig';
    }

    protected function getFormType(): string {
        return ExamGroupType::class;
    }

    protected function createNewEntity(): object {
        return new ExamGroup();
    }

    protected function getIndexRoute(): string {
        return 'app_maintainers_clinical_exam_group_index';
    }

    protected function getPageTitle(?string $action = null): string {
        return match($action) {
            'create' => $this->translator->trans('maintainers.titles.create_exam_group', [], 'maintainers'),
            'edit' => $this->translator->trans('maintainers.titles.edit_exam_group', [], 'maintainers'),
            default => $this->translator->trans('maintainers.titles.exam_group_list', [], 'maintainers')
        };
    }

    protected function beforeSave(object $entity, Request $request): void {
        if ($entity instanceof ExamGroup) {
            $entity->setUpdatedAt(new \DateTime());
        }
    }

    protected function canDelete(object $entity): bool {
        return true;
    }
}
