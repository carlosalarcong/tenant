<?php

namespace App\Controller\Budget;

use App\Repository\Tenant\BudgetRepository;
use App\Repository\Tenant\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/budget')]
class BudgetController extends AbstractController
{
    public function __construct(
        private BudgetRepository $budgetRepository,
        private PersonRepository $personRepository,
    ) {}

    #[Route('', name: 'app_budget_index', methods: ['GET'])]
    public function index(): Response
    {
        $recentBudgets = $this->budgetRepository->findBy([], ['createdAt' => 'DESC'], 10);

        return $this->render('budget/index.html.twig', [
            'recentBudgets' => $recentBudgets,
        ]);
    }

    #[Route('/patient/{personId}/budgets', name: 'app_budget_list_for_patient', methods: ['GET'])]
    public function listForPatient(Request $request, int $personId): Response
    {
        $person = $this->personRepository->find($personId);
        if (!$person) {
            throw $this->createNotFoundException('Paciente no encontrado.');
        }

        $budgets = $this->budgetRepository->findBy(['person' => $person], ['createdAt' => 'DESC']);

        if ($request->headers->get('Sec-Fetch-Dest') === 'turboframe') {
            return $this->render('budget/_budget_list.html.twig', [
                'person'  => $person,
                'budgets' => $budgets,
            ]);
        }

        return $this->redirectToRoute('app_budget_index');
    }
}
