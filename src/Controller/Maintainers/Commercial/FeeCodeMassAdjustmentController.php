<?php

namespace App\Controller\Maintainers\Commercial;

use App\Entity\Tenant\FeeCodeMassAdjustment;
use App\Entity\Tenant\Member;
use App\Form\Maintainers\Commercial\MassAdjustmentType;
use App\Repository\Tenant\FeeCodeMassAdjustmentRepository;
use App\Repository\Tenant\FeeCodePriceRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/maintainers/commercial/fee-code-mass-adjustment')]
class FeeCodeMassAdjustmentController extends AbstractController
{
    public function __construct(
        private TenantEntityManager $tenantEntityManager,
        private FeeCodeMassAdjustmentRepository $adjustmentRepository,
        private FeeCodePriceRepository $feeCodePriceRepository
    ) {
    }

    #[Route('', name: 'app_maintainers_commercial_fee_code_mass_adjustment_index', methods: ['GET'])]
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

        return $this->render('maintainers/commercial/fee_code_mass_adjustment/index.html.twig', [
            'data' => $adjustments,
            'page_title' => 'Ajuste Masivo de Guarismos',
            'create_route' => 'app_maintainers_commercial_fee_code_mass_adjustment_create',
            'request' => $request,
        ]);
    }

    #[Route('/create', name: 'app_maintainers_commercial_fee_code_mass_adjustment_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $adjustment = new FeeCodeMassAdjustment();
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

            $prices = $this->feeCodePriceRepository->createQueryBuilder('fcp')
                ->leftJoin('fcp.feeCode', 'fc')
                ->addSelect('fc')
                ->where('fcp.branchPayer = :branchPayer')
                ->setParameter('branchPayer', $adjustment->getBranchPayer())
                ->getQuery()
                ->getResult();

            $feeCodeIds = [];
            foreach ($prices as $price) {
                $price->setAmount($this->multiplyAmount($price->getAmount(), $multiplier));
                $feeCodeIds[$price->getFeeCode()?->getId() ?? spl_object_id($price)] = true;
            }

            $adjustment
                ->setConfirmedCount(count($prices))
                ->setPlanCount(count($feeCodeIds));

            $this->tenantEntityManager->flush();
            $this->addFlash('success', 'Ajuste masivo aplicado correctamente.');

            return $this->redirectToRoute('app_maintainers_commercial_fee_code_mass_adjustment_index');
        }

        return $this->renderFormTemplate(
            $request,
            $form->createView(),
            'app_maintainers_commercial_fee_code_mass_adjustment_create',
            'Nuevo ajuste masivo de guarismos'
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
            'cancel_route' => 'app_maintainers_commercial_fee_code_mass_adjustment_index',
        ];

        if ($this->isTurboFrame($request)) {
            return $this->render('maintainers/_modal_form.html.twig', $parameters);
        }

        return $this->render('maintainers/commercial/mass_adjustment_form.html.twig', $parameters);
    }
}
