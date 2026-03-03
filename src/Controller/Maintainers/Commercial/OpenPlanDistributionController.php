<?php

namespace App\Controller\Maintainers\Commercial;

use App\Entity\Tenant\OpenPlanDistribution;
use App\Form\Maintainers\Commercial\OpenPlanDistributionType;
use App\Repository\Tenant\OpenPlanDistributionRepository;
use App\Repository\Tenant\OpenPlanPriceRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/maintainers/commercial/open-plan-price/{priceId}/distribution')]
class OpenPlanDistributionController extends AbstractController
{
    public function __construct(
        private TenantEntityManager $tenantEntityManager,
        private OpenPlanPriceRepository $openPlanPriceRepository,
        private OpenPlanDistributionRepository $openPlanDistributionRepository
    ) {
    }

    #[Route('', name: 'app_maintainers_commercial_open_plan_distribution_index', methods: ['GET'])]
    public function index(int $priceId): Response
    {
        $openPlanPrice = $this->openPlanPriceRepository->find($priceId);
        if (!$openPlanPrice) {
            throw $this->createNotFoundException('Open plan price not found');
        }

        return $this->render('maintainers/commercial/open_plan_distribution/index.html.twig', [
            'openPlanPrice' => $openPlanPrice,
            'data' => $openPlanPrice->getDistributions()->toArray(),
            'columns' => [
                'surgeryFeeItem.name' => 'Ítem Equipo Médico',
                'amount' => 'Monto',
                'isActive' => 'Activo',
            ],
        ]);
    }

    #[Route('/create', name: 'app_maintainers_commercial_open_plan_distribution_create', methods: ['GET', 'POST'])]
    public function create(int $priceId, Request $request): Response
    {
        $openPlanPrice = $this->openPlanPriceRepository->find($priceId);
        if (!$openPlanPrice) {
            throw $this->createNotFoundException('Open plan price not found');
        }

        $distribution = new OpenPlanDistribution();
        $distribution->setOpenPlanPrice($openPlanPrice);

        $form = $this->createForm(OpenPlanDistributionType::class, $distribution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->tenantEntityManager->persist($distribution);
            $this->tenantEntityManager->flush();

            return $this->redirectToRoute('app_maintainers_commercial_open_plan_distribution_index', [
                'priceId' => $priceId,
            ]);
        }

        if ($this->isTurboFrame($request)) {
            return $this->render('maintainers/_modal_form.html.twig', [
                'form' => $form->createView(),
                'action_url' => $this->generateUrl('app_maintainers_commercial_open_plan_distribution_create', [
                    'priceId' => $priceId,
                ]),
            ]);
        }

        return $this->render('maintainers/_modal_form.html.twig', [
            'form' => $form->createView(),
            'action_url' => $this->generateUrl('app_maintainers_commercial_open_plan_distribution_create', [
                'priceId' => $priceId,
            ]),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_open_plan_distribution_edit', methods: ['GET', 'POST'])]
    public function edit(int $priceId, int $id, Request $request): Response
    {
        $openPlanPrice = $this->openPlanPriceRepository->find($priceId);
        if (!$openPlanPrice) {
            throw $this->createNotFoundException('Open plan price not found');
        }

        $distribution = $this->openPlanDistributionRepository->find($id);
        if (!$distribution || $distribution->getOpenPlanPrice()?->getId() !== $openPlanPrice->getId()) {
            throw $this->createNotFoundException('Open plan distribution not found');
        }

        $form = $this->createForm(OpenPlanDistributionType::class, $distribution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->tenantEntityManager->flush();

            return $this->redirectToRoute('app_maintainers_commercial_open_plan_distribution_index', [
                'priceId' => $priceId,
            ]);
        }

        if ($this->isTurboFrame($request)) {
            return $this->render('maintainers/_modal_form.html.twig', [
                'form' => $form->createView(),
                'action_url' => $this->generateUrl('app_maintainers_commercial_open_plan_distribution_edit', [
                    'priceId' => $priceId,
                    'id' => $id,
                ]),
            ]);
        }

        return $this->render('maintainers/_modal_form.html.twig', [
            'form' => $form->createView(),
            'action_url' => $this->generateUrl('app_maintainers_commercial_open_plan_distribution_edit', [
                'priceId' => $priceId,
                'id' => $id,
            ]),
        ]);
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_open_plan_distribution_delete', methods: ['DELETE'])]
    public function delete(int $priceId, int $id, Request $request): Response
    {
        $openPlanPrice = $this->openPlanPriceRepository->find($priceId);
        if (!$openPlanPrice) {
            throw $this->createNotFoundException('Open plan price not found');
        }

        $distribution = $this->openPlanDistributionRepository->find($id);
        if (!$distribution || $distribution->getOpenPlanPrice()?->getId() !== $openPlanPrice->getId()) {
            throw $this->createNotFoundException('Open plan distribution not found');
        }

        if ($this->isCsrfTokenValid('delete_distribution' . $distribution->getId(), (string) $request->request->get('_token'))) {
            $this->tenantEntityManager->remove($distribution);
            $this->tenantEntityManager->flush();
        }

        return $this->redirectToRoute('app_maintainers_commercial_open_plan_distribution_index', [
            'priceId' => $priceId,
        ]);
    }

    private function isTurboFrame(Request $request): bool
    {
        return $request->headers->get('Sec-Fetch-Dest') === 'turboframe';
    }
}
