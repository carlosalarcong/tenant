<?php

namespace App\Controller\Maintainers\Commercial;

use App\Repository\Tenant\BranchPayerRepository;
use App\Repository\Tenant\BranchRepository;
use App\Repository\Tenant\PayerRepository;
use App\Repository\Tenant\PayerTypeRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/maintainers/commercial/price-manager')]
class PriceManagerController extends AbstractController
{
    public function __construct(
        private BranchRepository $branchRepository,
        private PayerTypeRepository $payerTypeRepository,
        private PayerRepository $payerRepository,
        private BranchPayerRepository $branchPayerRepository,
    ) {}

    #[Route('', name: 'app_maintainers_commercial_price_manager_filter', methods: ['GET', 'POST'])]
    public function filter(Request $request): Response
    {
        $branches   = $this->branchRepository->findBy(['isActive' => true], ['name' => 'ASC']);
        $payerTypes = $this->payerTypeRepository->findBy([], ['name' => 'ASC']);
        $payers     = $this->payerRepository->findBy(['isActive' => true], ['name' => 'ASC']);

        if ($request->isMethod('POST')) {
            $branchId  = (int) $request->request->get('branch_id');
            $payerId   = (int) $request->request->get('payer_id');

            $branchPayer = $this->branchPayerRepository->findOneBy([
                'branch' => $branchId,
                'payer'  => $payerId,
            ]);

            if (!$branchPayer) {
                $this->addFlash('error', 'No existe una relación Sucursal-Financiador para la selección realizada.');
                return $this->render('maintainers/commercial/price_manager/filter.html.twig', [
                    'branches'    => $branches,
                    'payer_types' => $payerTypes,
                    'payers'      => $payers,
                ]);
            }

            return $this->redirectToRoute('app_maintainers_commercial_price_manager_modalities', [
                'branchPayerId' => $branchPayer->getId(),
            ]);
        }

        return $this->render('maintainers/commercial/price_manager/filter.html.twig', [
            'branches'    => $branches,
            'payer_types' => $payerTypes,
            'payers'      => $payers,
        ]);
    }

    #[Route('/payers-by-type/{payerTypeId}', name: 'app_maintainers_commercial_price_manager_payers_by_type', methods: ['GET'])]
    public function payersByType(int $payerTypeId): JsonResponse
    {
        $payers = $this->payerRepository->findBy(
            ['payerType' => $payerTypeId, 'isActive' => true],
            ['name' => 'ASC']
        );

        return $this->json(array_map(
            fn($p) => ['id' => $p->getId(), 'name' => $p->getName()],
            $payers
        ));
    }

    #[Route('/{branchPayerId}', name: 'app_maintainers_commercial_price_manager_modalities', methods: ['GET'])]
    public function modalities(int $branchPayerId): Response
    {
        $branchPayer = $this->branchPayerRepository->find($branchPayerId);

        if (!$branchPayer) {
            return $this->redirectToRoute('app_maintainers_commercial_price_manager_filter');
        }

        $modalities = [
            [
                'label' => 'Honorarios y Prestaciones',
                'route' => 'app_maintainers_commercial_insurance_plan_index',
                'params' => ['branchPayerId' => $branchPayerId],
            ],
            [
                'label' => 'Pabellón y Sala de Procedimientos',
                'route' => 'app_maintainers_commercial_open_plan_price_index',
                'params' => ['branchPayerId' => $branchPayerId],
            ],
            [
                'label' => 'Paquete Integral',
                'route' => 'app_maintainers_commercial_surgery_package_plan_index',
                'params' => ['branchPayerId' => $branchPayerId],
            ],
            [
                'label' => 'Paquete Prestaciones',
                'route' => 'app_maintainers_commercial_service_package_index',
                'params' => ['branchPayerId' => $branchPayerId],
            ],
        ];

        return $this->render('maintainers/commercial/price_manager/modalities.html.twig', [
            'branch_payer' => $branchPayer,
            'modalities'   => $modalities,
        ]);
    }
}
