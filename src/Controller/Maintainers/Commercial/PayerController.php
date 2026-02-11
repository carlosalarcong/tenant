<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\Payer;
use App\Form\Maintainers\Commercial\PayerType;
use App\Repository\Tenant\PayerRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/payer')]
class PayerController extends AbstractMantenedorController
{
    public function __construct(
        private PayerRepository $payerRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->payerRepository->createQueryBuilder('p')
            ->leftJoin('p.payerType', 'pt')
            ->addSelect('pt')
            ->orderBy('p.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'id' => $this->translator->trans('maintainers.columns.id', [], 'maintainers'),
        'code' => $this->translator->trans('maintainers.columns.code', [], 'maintainers'),
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'payerType.name' => 'PayerType.name',
        'rut' => 'RUT',
        'phone' => 'Phone',
        'requiresAuthorization' => 'RequiresAuthorization',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/payer/index.html.twig';
    }

    protected function getFormType(): string
    {
        return PayerType::class;
    }

    protected function createNewEntity(): object
    {
        return new Payer();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_payer_index';
    }

    #[Route('', name: 'app_maintainers_commercial_payer_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_payer_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_payer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Payer $payer): Response
    {
        return $this->handleEdit($request, $payer->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_payer_delete', methods: ['DELETE'])]
    public function delete(Request $request, Payer $payer): Response
    {
        return $this->handleDelete($request, $payer->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_payer_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
