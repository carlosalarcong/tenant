<?php

namespace App\Controller\Maintainers\Hospital;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\ClinicalActionQuestion;
use App\Form\Maintainers\Hospital\ClinicalActionQuestionType;
use App\Repository\Tenant\ClinicalActionQuestionRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/hospital/clinical-action-question')]
class ClinicalActionQuestionController extends AbstractMantenedorController
{
    public function __construct(
        private readonly ClinicalActionQuestionRepository $repository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_hospital_clinical_action_question_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_hospital_clinical_action_question_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_hospital_clinical_action_question_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_hospital_clinical_action_question_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }

    #[Route('/export', name: 'app_maintainers_hospital_clinical_action_question_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_hospital_clinical_action_question_index';
    }

    protected function getEntityClass(): string
    {
        return ClinicalActionQuestion::class;
    }

    protected function getFormType(): string
    {
        return ClinicalActionQuestionType::class;
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/hospital/clinical_action_question/index.html.twig';
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->repository->createQueryBuilder('caq')
            ->leftJoin('caq.clinicalActionCategory', 'cac')
            ->addSelect('cac')
            ->orderBy('caq.sortOrder', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'sortOrder' => 'Orden',
        'clinicalActionCategory.name' => 'Categoria',
        'fieldType' => 'Tipo Campo',
        'isRequired' => 'Obligatorio',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function createNewEntity(): object
    {
        return new ClinicalActionQuestion();
    }

    protected function getExportColumns(): array
    {
        return ['name', 'sortOrder', 'clinicalActionCategory.name', 'fieldType', 'isRequired', 'isActive'];
    }

    protected function getExportHeaders(): array
    {
        return ['Nombre', 'Orden', 'Categoria', 'Tipo Campo', 'Obligatorio', 'Activo'];
    }

    protected function getExportFileName(): string
    {
        return 'preguntas_accion_clinica_' . date('Y-m-d') . '.csv';
    }

    protected function beforeSave(object $entity, Request $request): void
    {
        $entity->setUpdatedAt(new \DateTime());
    }

    protected function canDelete(object $entity): bool
    {
        // Add custom validation logic if needed
        return true;
    }

}
