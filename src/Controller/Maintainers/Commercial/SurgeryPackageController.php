<?php

namespace App\Controller\Maintainers\Commercial;

use App\Entity\Tenant\Member;
use App\Entity\Tenant\SurgeryPackage;
use App\Form\Maintainers\Commercial\SurgeryPackageType;
use App\Repository\Tenant\SurgeryPackagePlanRepository;
use App\Repository\Tenant\SurgeryPackageRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/maintainers/commercial/surgery-package-plan/{planId}/package')]
class SurgeryPackageController extends AbstractController
{
    public function __construct(
        private TenantEntityManager $tenantEntityManager,
        private SurgeryPackagePlanRepository $surgeryPackagePlanRepository,
        private SurgeryPackageRepository $surgeryPackageRepository
    ) {
    }

    #[Route('', name: 'app_maintainers_commercial_surgery_package_index', methods: ['GET'])]
    public function index(int $planId): Response
    {
        $plan = $this->surgeryPackagePlanRepository->find($planId);
        if (!$plan) {
            throw $this->createNotFoundException('Surgery package plan not found');
        }

        return $this->render('maintainers/commercial/surgery_package/index.html.twig', [
            'plan' => $plan,
            'data' => $plan->getPackages()->toArray(),
            'columns' => [
                'name' => 'Nombre',
                'billingItem.name' => 'Prestación Principal',
                'adjustmentPercentage' => '% Ajuste',
                'isActive' => 'Activo',
            ],
        ]);
    }

    #[Route('/create', name: 'app_maintainers_commercial_surgery_package_create', methods: ['GET', 'POST'])]
    public function create(int $planId, Request $request): Response
    {
        $plan = $this->surgeryPackagePlanRepository->find($planId);
        if (!$plan) {
            throw $this->createNotFoundException('Surgery package plan not found');
        }

        $package = new SurgeryPackage();
        $package->setPlan($plan);

        $user = $this->getUser();
        if ($user instanceof Member) {
            $package->setCreatedBy($user);
        }

        $form = $this->createForm(SurgeryPackageType::class, $package);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->tenantEntityManager->persist($package);
            $this->tenantEntityManager->flush();

            return $this->redirectToRoute('app_maintainers_commercial_surgery_package_index', [
                'planId' => $planId,
            ]);
        }

        if ($this->isTurboFrame($request)) {
            return $this->render('maintainers/_modal_form.html.twig', [
                'form' => $form->createView(),
                'action_url' => $this->generateUrl('app_maintainers_commercial_surgery_package_create', [
                    'planId' => $planId,
                ]),
            ]);
        }

        return $this->render('maintainers/_modal_form.html.twig', [
            'form' => $form->createView(),
            'action_url' => $this->generateUrl('app_maintainers_commercial_surgery_package_create', [
                'planId' => $planId,
            ]),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_surgery_package_edit', methods: ['GET', 'POST'])]
    public function edit(int $planId, int $id, Request $request): Response
    {
        $plan = $this->surgeryPackagePlanRepository->find($planId);
        if (!$plan) {
            throw $this->createNotFoundException('Surgery package plan not found');
        }

        $package = $this->surgeryPackageRepository->find($id);
        if (!$package || $package->getPlan()?->getId() !== $plan->getId()) {
            throw $this->createNotFoundException('Surgery package not found');
        }

        $form = $this->createForm(SurgeryPackageType::class, $package);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->tenantEntityManager->flush();

            return $this->redirectToRoute('app_maintainers_commercial_surgery_package_index', [
                'planId' => $planId,
            ]);
        }

        if ($this->isTurboFrame($request)) {
            return $this->render('maintainers/_modal_form.html.twig', [
                'form' => $form->createView(),
                'action_url' => $this->generateUrl('app_maintainers_commercial_surgery_package_edit', [
                    'planId' => $planId,
                    'id' => $id,
                ]),
            ]);
        }

        return $this->render('maintainers/_modal_form.html.twig', [
            'form' => $form->createView(),
            'action_url' => $this->generateUrl('app_maintainers_commercial_surgery_package_edit', [
                'planId' => $planId,
                'id' => $id,
            ]),
        ]);
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_surgery_package_delete', methods: ['DELETE'])]
    public function delete(int $planId, int $id, Request $request): Response
    {
        $plan = $this->surgeryPackagePlanRepository->find($planId);
        if (!$plan) {
            throw $this->createNotFoundException('Surgery package plan not found');
        }

        $package = $this->surgeryPackageRepository->find($id);
        if (!$package || $package->getPlan()?->getId() !== $plan->getId()) {
            throw $this->createNotFoundException('Surgery package not found');
        }

        if ($this->isCsrfTokenValid('delete_package' . $package->getId(), (string) $request->request->get('_token'))) {
            $this->tenantEntityManager->remove($package);
            $this->tenantEntityManager->flush();
        }

        return $this->redirectToRoute('app_maintainers_commercial_surgery_package_index', [
            'planId' => $planId,
        ]);
    }

    private function isTurboFrame(Request $request): bool
    {
        return $request->headers->get('Sec-Fetch-Dest') === 'turboframe';
    }
}
