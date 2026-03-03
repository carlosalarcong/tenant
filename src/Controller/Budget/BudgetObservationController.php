<?php

namespace App\Controller\Budget;

use App\Entity\Tenant\BudgetObservation;
use App\Entity\Tenant\Member;
use App\Repository\Tenant\BudgetRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/budget/{budgetId}/observations')]
class BudgetObservationController extends AbstractController
{
    public function __construct(
        private BudgetRepository $budgetRepository,
        private TenantEntityManager $tenantEntityManager,
    ) {}

    #[Route('', name: 'app_budget_observations_list', methods: ['GET'])]
    public function list(int $budgetId): Response
    {
        $budget = $this->budgetRepository->find($budgetId);
        if (!$budget) {
            throw $this->createNotFoundException('Presupuesto no encontrado.');
        }

        $observations = $budget->getObservations()->toArray();
        usort($observations, fn($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt());

        return $this->render('budget/_observations_list.html.twig', [
            'budget'       => $budget,
            'observations' => $observations,
        ]);
    }

    #[Route('', name: 'app_budget_observations_save', methods: ['POST'])]
    public function save(Request $request, int $budgetId): Response
    {
        $budget = $this->budgetRepository->find($budgetId);
        if (!$budget) {
            throw $this->createNotFoundException('Presupuesto no encontrado.');
        }

        $text = trim($request->request->get('observation', ''));
        if ($text === '') {
            return new JsonResponse(['error' => 'Texto requerido'], 400);
        }

        $obs = new BudgetObservation();
        $obs->setBudget($budget);
        $obs->setObservation($text);

        $user = $this->getUser();
        if ($user instanceof Member) {
            $obs->setMember($user);
        }

        $this->tenantEntityManager->persist($obs);
        $this->tenantEntityManager->flush();

        return new Response(
            $this->renderView('budget/_observation_item_stream.html.twig', [
                'observation' => $obs,
            ]),
            200,
            ['Content-Type' => 'text/vnd.turbo-stream.html']
        );
    }
}
