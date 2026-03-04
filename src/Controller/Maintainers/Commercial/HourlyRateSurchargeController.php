<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\HourlyRateSurcharge;
use App\Entity\Tenant\Member;
use App\Form\Maintainers\Commercial\HourlyRateSurchargeType;
use App\Repository\Tenant\HourlyRateSurchargeRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/hourly-rate-surcharge')]
class HourlyRateSurchargeController extends AbstractMantenedorController
{
    public function __construct(
        private HourlyRateSurchargeRepository $hourlyRateSurchargeRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->hourlyRateSurchargeRepository->createQueryBuilder('hrs')
            ->leftJoin('hrs.branchPayer', 'bp')
            ->addSelect('bp')
            ->leftJoin('bp.branch', 'b')
            ->addSelect('b')
            ->leftJoin('bp.payer', 'p')
            ->addSelect('p')
            ->leftJoin('hrs.createdBy', 'cb')
            ->addSelect('cb')
            ->orderBy('b.name', 'ASC')
            ->addOrderBy('p.name', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
            'branchPayer.branch.name' => 'Sucursal',
            'branchPayer.payer.name' => 'Financiador',
            'dayOfWeek' => 'Día',
            'startTime' => 'Inicio',
            'endTime' => 'Término',
            'percentage' => '% Recargo',
        ];
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/hourly_rate_surcharge/index.html.twig';
    }

    protected function getFormType(): string
    {
        return HourlyRateSurchargeType::class;
    }

    protected function createNewEntity(): object
    {
        return new HourlyRateSurcharge();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_hourly_rate_surcharge_index';
    }

    protected function beforeSave(object $entity, Request $request): void
    {
        if ($entity instanceof HourlyRateSurcharge && $entity->getCreatedBy() === null) {
            $user = $this->getUser();
            if ($user instanceof Member) {
                $entity->setCreatedBy($user);
            }
        }
    }

    #[Route('', name: 'app_maintainers_commercial_hourly_rate_surcharge_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_hourly_rate_surcharge_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_hourly_rate_surcharge_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, HourlyRateSurcharge $hourlyRateSurcharge): Response
    {
        return $this->handleEdit($request, $hourlyRateSurcharge->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_hourly_rate_surcharge_delete', methods: ['DELETE'])]
    public function delete(Request $request, HourlyRateSurcharge $hourlyRateSurcharge): Response
    {
        return $this->handleDelete($request, $hourlyRateSurcharge->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_hourly_rate_surcharge_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
