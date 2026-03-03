<?php

namespace App\Controller\Budget;

use App\Repository\Tenant\BudgetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/budget')]
class BudgetDetailController extends AbstractController
{
    public function __construct(
        private BudgetRepository $budgetRepository,
    ) {}

    #[Route('/{id}', name: 'app_budget_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $budget = $this->budgetRepository->find($id);
        if (!$budget) {
            throw $this->createNotFoundException('Presupuesto no encontrado.');
        }

        $detailsByType = [];
        foreach ($budget->getDetails() as $detail) {
            $detailsByType[$detail->getItemType()][] = $detail;
        }

        return $this->render('budget/show.html.twig', [
            'budget'        => $budget,
            'detailsByType' => $detailsByType,
        ]);
    }
}
