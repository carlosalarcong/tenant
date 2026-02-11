<?php

namespace App\Controller\Maintainers\Settlements;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\DailyUF;
use App\Form\Maintainers\Settlements\DailyUFType;
use App\Repository\Tenant\DailyUFRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/settlements/daily-uf')]
class DailyUFController extends AbstractMantenedorController
{
    public function __construct(
        private DailyUFRepository $dailyUFRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_settlements_daily_uf_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_settlements_daily_uf_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_settlements_daily_uf_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_settlements_daily_uf_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_settlements_daily_uf_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['ufDate', 'ufValue', 'isActive'],
            headers: $this->translateColumns(['uf_date', 'uf_value', 'is_active']),
            filename: 'uf_diaria_' . date('Y-m-d') . '.csv'
        );
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->dailyUFRepository->createQueryBuilder('duf')
            ->orderBy('duf.ufDate', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
            'ufDate' => $this->translator->trans('maintainers.columns.uf_date', [], 'maintainers'),
            'ufValue' => $this->translator->trans('maintainers.columns.uf_value', [], 'maintainers'),
            'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
        ];
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/settlements/daily_uf/index.html.twig';
    }

    protected function getFormType(): string
    {
        return DailyUFType::class;
    }

    protected function createNewEntity(): object
    {
        return new DailyUF();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_settlements_daily_uf_index';
    }

    protected function getPageTitle(?string $action = null): string
    {
        return match($action) {
            'create' => 'Crear UF Diaria',
            'edit' => 'Editar UF Diaria',
            default => 'UF Diaria'
        };
    }

    protected function beforeSave(object $entity, Request $request): void
    {
        if ($entity instanceof DailyUF) {
            $entity->setUpdatedAt(new \DateTime());
        }
    }

    protected function canDelete(object $entity): bool
    {
        return true;
    }
}
