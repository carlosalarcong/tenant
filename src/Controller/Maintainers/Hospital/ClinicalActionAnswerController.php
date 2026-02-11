<?php

namespace App\Controller\Maintainers\Hospital;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\ClinicalActionAnswer;
use App\Form\Maintainers\Hospital\ClinicalActionAnswerType;
use App\Repository\Tenant\ClinicalActionAnswerRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/hospital/clinical-action-answer')]
class ClinicalActionAnswerController extends AbstractMantenedorController
{
    public function __construct(
        private readonly ClinicalActionAnswerRepository $repository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_hospital_clinical_action_answer_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_hospital_clinical_action_answer_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_hospital_clinical_action_answer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_hospital_clinical_action_answer_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }

    #[Route('/export', name: 'app_maintainers_hospital_clinical_action_answer_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_hospital_clinical_action_answer_index';
    }

    protected function getEntityClass(): string
    {
        return ClinicalActionAnswer::class;
    }

    protected function getFormType(): string
    {
        return ClinicalActionAnswerType::class;
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/hospital/clinical_action_answer/index.html.twig';
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->repository->createQueryBuilder('caa')
            ->leftJoin('caa.clinicalActionQuestion', 'caq')
            ->addSelect('caq')
            ->orderBy('caq.sortOrder', 'ASC')
            ->addOrderBy('caa.sortOrder', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
        'sortOrder' => 'Orden',
        'preText' => 'Texto Previo',
        'clinicalActionQuestion.name' => 'Pregunta',
        'isChecked' => 'Seleccionado',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function createNewEntity(): object
    {
        return new ClinicalActionAnswer();
    }

    protected function getExportColumns(): array
    {
        return ['sortOrder', 'preText', 'clinicalActionQuestion.name', 'isChecked', 'isActive'];
    }

    protected function getExportHeaders(): array
    {
        return ['Orden', 'Texto Previo', 'Pregunta', 'Seleccionado', 'Activo'];
    }

    protected function getExportFileName(): string
    {
        return 'respuestas_accion_clinica_' . date('Y-m-d') . '.csv';
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
