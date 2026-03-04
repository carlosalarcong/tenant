<?php

namespace App\Controller\Maintainers\Commercial;

use App\Entity\Tenant\Member;
use App\Entity\Tenant\OpenPlanMassAdjustment;
use App\Form\Maintainers\Commercial\MassAdjustmentType;
use App\Repository\Tenant\OpenPlanMassAdjustmentRepository;
use App\Repository\Tenant\OpenPlanPriceRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/maintainers/commercial/open-plan-mass-adjustment')]
class OpenPlanMassAdjustmentController extends AbstractController
{
    public function __construct(
        private TenantEntityManager $tenantEntityManager,
        private OpenPlanMassAdjustmentRepository $adjustmentRepository,
        private OpenPlanPriceRepository $openPlanPriceRepository
    ) {
    }

    #[Route('', name: 'app_maintainers_commercial_open_plan_mass_adjustment_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $adjustments = $this->adjustmentRepository->createQueryBuilder('a')
            ->leftJoin('a.branchPayer', 'bp')
            ->addSelect('bp')
            ->leftJoin('bp.branch', 'b')
            ->addSelect('b')
            ->leftJoin('bp.payer', 'p')
            ->addSelect('p')
            ->leftJoin('a.createdBy', 'cb')
            ->addSelect('cb')
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('maintainers/commercial/open_plan_mass_adjustment/index.html.twig', [
            'data' => $adjustments,
            'page_title' => 'Ajuste Masivo de Cuenta Abierta',
            'create_route' => 'app_maintainers_commercial_open_plan_mass_adjustment_create',
            'request' => $request,
        ]);
    }

    #[Route('/create', name: 'app_maintainers_commercial_open_plan_mass_adjustment_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $adjustment = new OpenPlanMassAdjustment();
        $form = $this->createForm(MassAdjustmentType::class, $adjustment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $percentageInput = (float) $form->get('adjustmentPercentageInput')->getData();
            $multiplier = ($percentageInput / 100) + 1;

            $adjustment
                ->setAdjustmentPercentage(number_format($multiplier, 2, '.', ''))
                ->setEffectiveDate($form->get('effectiveDate')->getData())
                ->setBranchPayer($form->get('branchPayer')->getData())
                ->setCreatedBy($this->getCurrentMember());

            $this->tenantEntityManager->persist($adjustment);
            $this->tenantEntityManager->flush();

            $prices = $this->openPlanPriceRepository->createQueryBuilder('opp')
                ->leftJoin('opp.plan', 'plan')
                ->addSelect('plan')
                ->leftJoin('opp.distributions', 'distribution')
                ->addSelect('distribution')
                ->where('plan.branchPayer = :branchPayer')
                ->andWhere('opp.isActive = :active')
                ->setParameter('branchPayer', $adjustment->getBranchPayer())
                ->setParameter('active', true)
                ->getQuery()
                ->getResult();

            $planIds = [];
            foreach ($prices as $price) {
                $price->setUnitPrice($this->multiplyAmount($price->getUnitPrice(), $multiplier));
                $price->setCopayAmount($this->multiplyAmount($price->getCopayAmount(), $multiplier));
                $price->setTheatreAmount($this->multiplyAmount($price->getTheatreAmount(), $multiplier));

                foreach ($price->getDistributions() as $distribution) {
                    $distribution->setAmount($this->multiplyAmount($distribution->getAmount(), $multiplier));
                }

                $planIds[$price->getPlan()?->getId() ?? spl_object_id($price)] = true;
            }

            $adjustment
                ->setConfirmedCount(count($prices))
                ->setPlanCount(count($planIds));

            $this->tenantEntityManager->flush();
            $this->addFlash('success', 'Ajuste masivo aplicado correctamente.');

            return $this->redirectToRoute('app_maintainers_commercial_open_plan_mass_adjustment_index');
        }

        return $this->renderFormTemplate(
            $request,
            $form->createView(),
            'app_maintainers_commercial_open_plan_mass_adjustment_create',
            'Nuevo ajuste masivo de cuenta abierta'
        );
    }

    private function multiplyAmount(?string $value, float $multiplier): string
    {
        $newValue = round((float) $value * $multiplier, 2);

        return number_format($newValue, 2, '.', '');
    }

    private function getCurrentMember(): Member
    {
        $user = $this->getUser();
        if (!$user instanceof Member) {
            throw new AccessDeniedException('Authenticated member required.');
        }

        return $user;
    }

    private function isTurboFrame(Request $request): bool
    {
        return $request->headers->has('Turbo-Frame') || $request->headers->get('Sec-Fetch-Dest') === 'turboframe';
    }

    private function renderFormTemplate(Request $request, mixed $formView, string $routeName, string $pageTitle): Response
    {
        $parameters = [
            'form' => $formView,
            'action_url' => $this->generateUrl($routeName),
            'page_title' => $pageTitle,
            'cancel_route' => 'app_maintainers_commercial_open_plan_mass_adjustment_index',
        ];

        if ($this->isTurboFrame($request)) {
            return $this->render('maintainers/_modal_form.html.twig', $parameters);
        }

        return $this->render('maintainers/commercial/mass_adjustment_form.html.twig', $parameters);
    }
}
