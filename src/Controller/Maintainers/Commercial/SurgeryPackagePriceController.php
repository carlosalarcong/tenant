<?php

namespace App\Controller\Maintainers\Commercial;

use App\Entity\Tenant\SurgeryPackagePrice;
use App\Form\Maintainers\Commercial\SurgeryPackagePriceType;
use App\Repository\Tenant\SurgeryPackagePlanRepository;
use App\Repository\Tenant\SurgeryPackagePriceRepository;
use App\Repository\Tenant\SurgeryPackageRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/maintainers/commercial/surgery-package-plan/{planId}/package/{packageId}/price')]
class SurgeryPackagePriceController extends AbstractController
{
    public function __construct(
        private TenantEntityManager $tenantEntityManager,
        private SurgeryPackagePlanRepository $surgeryPackagePlanRepository,
        private SurgeryPackageRepository $surgeryPackageRepository,
        private SurgeryPackagePriceRepository $surgeryPackagePriceRepository
    ) {
    }

    #[Route('', name: 'app_maintainers_commercial_surgery_package_price_index', methods: ['GET'])]
    public function index(int $planId, int $packageId): Response
    {
        [$plan, $package] = $this->resolvePlanAndPackage($planId, $packageId);

        return $this->render('maintainers/commercial/surgery_package_price/index.html.twig', [
            'plan' => $plan,
            'package' => $package,
            'data' => $package->getPrices()->toArray(),
            'columns' => [
                'surgeryFeeItem.name' => 'Ítem',
                'payerPrice' => 'Precio Isapre',
                'clinicPrice' => 'Precio Clínica',
                'isInUse' => 'En Uso',
            ],
        ]);
    }

    #[Route('/create', name: 'app_maintainers_commercial_surgery_package_price_create', methods: ['GET', 'POST'])]
    public function create(int $planId, int $packageId, Request $request): Response
    {
        [$plan, $package] = $this->resolvePlanAndPackage($planId, $packageId);

        $price = new SurgeryPackagePrice();
        $price->setPackage($package);

        $form = $this->createForm(SurgeryPackagePriceType::class, $price);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->tenantEntityManager->persist($price);
            $this->tenantEntityManager->flush();

            return $this->redirectToRoute('app_maintainers_commercial_surgery_package_price_index', [
                'planId' => $planId,
                'packageId' => $packageId,
            ]);
        }

        if ($this->isTurboFrame($request)) {
            return $this->render('maintainers/_modal_form.html.twig', [
                'form' => $form->createView(),
                'action_url' => $this->generateUrl('app_maintainers_commercial_surgery_package_price_create', [
                    'planId' => $planId,
                    'packageId' => $packageId,
                ]),
            ]);
        }

        return $this->render('maintainers/_modal_form.html.twig', [
            'form' => $form->createView(),
            'action_url' => $this->generateUrl('app_maintainers_commercial_surgery_package_price_create', [
                'planId' => $planId,
                'packageId' => $packageId,
            ]),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_surgery_package_price_edit', methods: ['GET', 'POST'])]
    public function edit(int $planId, int $packageId, int $id, Request $request): Response
    {
        [$plan, $package] = $this->resolvePlanAndPackage($planId, $packageId);

        $price = $this->surgeryPackagePriceRepository->find($id);
        if (!$price || $price->getPackage()?->getId() !== $package->getId()) {
            throw $this->createNotFoundException('Surgery package price not found');
        }

        $form = $this->createForm(SurgeryPackagePriceType::class, $price);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->tenantEntityManager->flush();

            return $this->redirectToRoute('app_maintainers_commercial_surgery_package_price_index', [
                'planId' => $planId,
                'packageId' => $packageId,
            ]);
        }

        if ($this->isTurboFrame($request)) {
            return $this->render('maintainers/_modal_form.html.twig', [
                'form' => $form->createView(),
                'action_url' => $this->generateUrl('app_maintainers_commercial_surgery_package_price_edit', [
                    'planId' => $planId,
                    'packageId' => $packageId,
                    'id' => $id,
                ]),
            ]);
        }

        return $this->render('maintainers/_modal_form.html.twig', [
            'form' => $form->createView(),
            'action_url' => $this->generateUrl('app_maintainers_commercial_surgery_package_price_edit', [
                'planId' => $planId,
                'packageId' => $packageId,
                'id' => $id,
            ]),
        ]);
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_surgery_package_price_delete', methods: ['DELETE'])]
    public function delete(int $planId, int $packageId, int $id, Request $request): Response
    {
        [, $package] = $this->resolvePlanAndPackage($planId, $packageId);

        $price = $this->surgeryPackagePriceRepository->find($id);
        if (!$price || $price->getPackage()?->getId() !== $package->getId()) {
            throw $this->createNotFoundException('Surgery package price not found');
        }

        if ($this->isCsrfTokenValid('delete_package_price' . $price->getId(), (string) $request->request->get('_token'))) {
            $this->tenantEntityManager->remove($price);
            $this->tenantEntityManager->flush();
        }

        return $this->redirectToRoute('app_maintainers_commercial_surgery_package_price_index', [
            'planId' => $planId,
            'packageId' => $packageId,
        ]);
    }

    private function isTurboFrame(Request $request): bool
    {
        return $request->headers->get('Sec-Fetch-Dest') === 'turboframe';
    }

    private function resolvePlanAndPackage(int $planId, int $packageId): array
    {
        $plan = $this->surgeryPackagePlanRepository->find($planId);
        if (!$plan) {
            throw $this->createNotFoundException('Surgery package plan not found');
        }

        $package = $this->surgeryPackageRepository->find($packageId);
        if (!$package || $package->getPlan()?->getId() !== $plan->getId()) {
            throw $this->createNotFoundException('Surgery package not found');
        }

        return [$plan, $package];
    }
}
