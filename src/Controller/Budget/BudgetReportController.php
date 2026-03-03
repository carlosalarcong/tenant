<?php

namespace App\Controller\Budget;

use App\Repository\Tenant\BranchRepository;
use App\Repository\Tenant\BudgetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/budget/report')]
class BudgetReportController extends AbstractController
{
    public function __construct(
        private BudgetRepository $budgetRepository,
        private BranchRepository $branchRepository,
    ) {}

    #[Route('', name: 'app_budget_report', methods: ['GET', 'POST'])]
    public function __invoke(Request $request): Response
    {
        $status    = $request->get('status', '');
        $rawFrom   = $request->get('dateFrom', '');
        $rawTo     = $request->get('dateTo', '');
        $rawMember = $request->get('memberId', '');
        $rawBranch = $request->get('branchId', '');
        $branchId  = $rawBranch ? (int) $rawBranch : null;

        $dateFrom = null;
        if ($rawFrom) {
            try { $dateFrom = new \DateTime($rawFrom); } catch (\Exception) {}
        }

        $dateTo = null;
        if ($rawTo) {
            try { $dateTo = new \DateTime($rawTo); } catch (\Exception) {}
        }

        $memberId = null;
        if ($rawMember) {
            $memberId = (int) $rawMember;
        }

        $hasFilters = $request->isMethod('POST')
            || $rawFrom || $rawTo || $rawMember || $status || $rawBranch;

        $budgets = $hasFilters
            ? $this->budgetRepository->findByFilters(
                $dateFrom, $dateTo, $memberId ?: null, $status ?: null, $branchId
            )
            : [];

        if ($request->get('export') === 'csv' && $hasFilters) {
            return $this->exportCsv($budgets);
        }

        $totalAmount = 0;
        foreach ($budgets as $b) {
            foreach ($b->getDetails() as $d) {
                $totalAmount += (float) $d->getAmount();
            }
        }

        return $this->render('budget/report.html.twig', [
            'budgets'     => $budgets,
            'totalAmount' => $totalAmount,
            'branches'    => $this->branchRepository->findBy(['isActive' => true], ['name' => 'ASC']),
            'filters'     => [
                'dateFrom' => $rawFrom,
                'dateTo'   => $rawTo,
                'memberId' => $rawMember,
                'status'   => $status,
                'branchId' => $rawBranch,
            ],
            'hasFilters'  => $hasFilters,
        ]);
    }

    private function exportCsv(array $budgets): Response
    {
        $rows[] = ['#', 'Paciente', 'RUT', 'Fecha', 'Modalidad', 'Plan',
                   'Financiador', 'Estado', 'Monto Total'];
        foreach ($budgets as $b) {
            $total = array_sum(array_map(
                fn($d) => (float) $d->getAmount(),
                $b->getDetails()->toArray()
            ));
            $rows[] = [
                $b->getNumber(),
                $b->getPerson()->getFirstName() . ' ' . $b->getPerson()->getLastName(),
                $b->getPerson()->getRut(),
                $b->getCreatedAt()->format('d/m/Y'),
                $b->getInsurancePlan() ? 'Abierta' : 'Paquetizado',
                $b->getInsurancePlan()?->getName()
                    ?? $b->getSurgeryPackagePlan()?->getName() ?? '',
                $b->getPayer()?->getName() ?? '',
                $b->getStatus(),
                number_format($total, 0, ',', '.'),
            ];
        }
        $csv = implode("\n", array_map(
            fn($r) => implode(';', array_map(
                fn($v) => '"' . str_replace('"', '""', (string) $v) . '"', $r
            )),
            $rows
        ));

        return new Response("\xEF\xBB\xBF" . $csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="informe-presupuestos.csv"',
        ]);
    }
}
