<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\ExternalReferrer;
use App\Form\Maintainers\Commercial\ExternalReferrerType;
use App\Repository\Tenant\ExternalReferrerRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/external-referrer')]
class ExternalReferrerController extends AbstractMantenedorController
{
    public function __construct(
        private ExternalReferrerRepository $externalReferrerRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->externalReferrerRepository->createQueryBuilder('er')
            ->orderBy('er.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'id' => $this->translator->trans('maintainers.columns.id', [], 'maintainers'),
        'code' => $this->translator->trans('maintainers.columns.code', [], 'maintainers'),
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'referrerType' => 'ReferrerType',
        'phone' => 'Phone',
        'hasAgreement' => 'HasAgreement',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/external_referrer/index.html.twig';
    }

    protected function getFormType(): string
    {
        return ExternalReferrerType::class;
    }

    protected function createNewEntity(): object
    {
        return new ExternalReferrer();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_external_referrer_index';
    }

    #[Route('', name: 'app_maintainers_commercial_external_referrer_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_external_referrer_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_external_referrer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ExternalReferrer $externalReferrer): Response
    {
        return $this->handleEdit($request, $externalReferrer->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_external_referrer_delete', methods: ['DELETE'])]
    public function delete(Request $request, ExternalReferrer $externalReferrer): Response
    {
        return $this->handleDelete($request, $externalReferrer->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_external_referrer_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
