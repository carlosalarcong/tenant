<?php

namespace App\Controller\Maintainers\Admission;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\EmergencyConsultationType;
use App\Form\Maintainers\Admission\EmergencyConsultationTypeType;
use App\Repository\Tenant\EmergencyConsultationTypeRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * EmergencyConsultationType Controller
 * 
 * Gestiona el mantenedor de Tipos de Consulta de Urgencia
 */
#[Route('/maintainers/admission/emergency-consultation-type')]
class EmergencyConsultationTypeController extends AbstractMantenedorController
{
    public function __construct(
        private EmergencyConsultationTypeRepository $emergencyConsultationTypeRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_admission_emergency_consultation_type_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_admission_emergency_consultation_type_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_admission_emergency_consultation_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_admission_emergency_consultation_type_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_admission_emergency_consultation_type_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'isActive'],
            headers: $this->translateColumns(['name', 'is_active']),
            filename: 'tipos_consulta_urgencia_' . date('Y-m-d') . '.csv'
        );
    }

    // ========================================================================
    // Implementación de métodos abstractos
    // ========================================================================

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->emergencyConsultationTypeRepository->createQueryBuilder('ect')
            ->orderBy('ect.id', 'DESC');
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
        return 'maintainers/admission/emergency_consultation_type/index.html.twig';
    }

    protected function getFormType(): string
    {
        return EmergencyConsultationTypeType::class;
    }

    protected function createNewEntity(): object
    {
        return new EmergencyConsultationType();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_admission_emergency_consultation_type_index';
    }

    protected function getPageTitle(?string $action = null): string
    {
        return match($action) {
            'create' => 'Crear Tipo Consulta Urgencia',
            'edit' => 'Editar Tipo Consulta Urgencia',
            default => 'Tipos de Consulta Urgencia'
        };
    }

    // ========================================================================
    // Hooks personalizados (opcional)
    // ========================================================================

    protected function beforeSave(object $entity, Request $request): void
    {
        if ($entity instanceof EmergencyConsultationType) {
            $entity->setUpdatedAt(new \DateTime());
        }
    }

    protected function canDelete(object $entity): bool
    {
        return true;
    }
}
