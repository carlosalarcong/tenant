<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\FeeCodePrice;
use App\Entity\Tenant\Member;
use App\Form\Maintainers\Commercial\FeeCodePriceType;
use App\Repository\Tenant\FeeCodePriceRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/fee-code-price')]
class FeeCodePriceController extends AbstractMantenedorController
{
    public function __construct(
        private FeeCodePriceRepository $feeCodePriceRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->feeCodePriceRepository->createQueryBuilder('fcp')
            ->leftJoin('fcp.feeCode', 'fc')
            ->addSelect('fc')
            ->leftJoin('fcp.branchPayer', 'bp')
            ->addSelect('bp')
            ->leftJoin('bp.branch', 'b')
            ->addSelect('b')
            ->leftJoin('bp.payer', 'p')
            ->addSelect('p')
            ->leftJoin('fcp.modifiedBy', 'mb')
            ->addSelect('mb')
            ->orderBy('b.name', 'ASC')
            ->addOrderBy('fc.name', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
            'id' => 'ID',
            'feeCode.name' => 'Guarismo',
            'branchPayer.branch.name' => 'Sucursal',
            'branchPayer.payer.name' => 'Financiador',
            'amount' => 'Valor',
            'effectiveDate' => 'F. Vigencia',
            'modifiedAt' => 'Modificado',
        ];
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/fee_code_price/index.html.twig';
    }

    protected function getFormType(): string
    {
        return FeeCodePriceType::class;
    }

    protected function createNewEntity(): object
    {
        return new FeeCodePrice();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_fee_code_price_index';
    }

    protected function beforeSave(object $entity, Request $request): void
    {
        if ($entity instanceof FeeCodePrice) {
            $user = $this->getUser();
            if ($user instanceof Member) {
                $entity->setModifiedBy($user);
            }
        }
    }

    #[Route('', name: 'app_maintainers_commercial_fee_code_price_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_fee_code_price_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_fee_code_price_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, FeeCodePrice $feeCodePrice): Response
    {
        return $this->handleEdit($request, $feeCodePrice->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_fee_code_price_delete', methods: ['DELETE'])]
    public function delete(Request $request, FeeCodePrice $feeCodePrice): Response
    {
        return $this->handleDelete($request, $feeCodePrice->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_fee_code_price_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
