<?php

namespace App\Controller\Budget;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\Budget;
use App\Entity\Tenant\BudgetDetail;
use App\Entity\Tenant\BudgetObservation;
use App\Repository\Tenant\BudgetRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/budget', name: 'budget_')]
class BudgetDetailController extends AbstractTenantAwareController
{
    public function __construct(
        private readonly BudgetRepository $budgetRepository,
    ) {}

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $budget = $this->budgetRepository->createQueryBuilder('b')
            ->leftJoin('b.person', 'person')
            ->leftJoin('b.payer', 'payer')
            ->leftJoin('b.agreement', 'agreement')
            ->leftJoin('b.insurancePlan', 'insurancePlan')
            ->leftJoin('b.surgeryPackagePlan', 'surgeryPackagePlan')
            ->leftJoin('b.professional', 'professional')
            ->leftJoin('b.careType', 'careType')
            ->leftJoin('b.origin', 'origin')
            ->leftJoin('b.details', 'details')
            ->leftJoin('details.medicalService', 'medicalService')
            ->leftJoin('details.surgeryPackageItem', 'surgeryPackageItem')
            ->addSelect(
                'person',
                'payer',
                'agreement',
                'insurancePlan',
                'surgeryPackagePlan',
                'professional',
                'careType',
                'origin',
                'details',
                'medicalService',
                'surgeryPackageItem'
            )
            ->where('b.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$budget instanceof Budget) {
            throw $this->createNotFoundException('Presupuesto no encontrado.');
        }

        $groupedDetails = [
            'honorarios' => [],
            'pabellon' => [],
            'clinicos' => [],
            'valorizables' => [],
            'examenes' => [],
            'diaCama' => [],
        ];

        $totals = [
            'honorarios' => 0.0,
            'pabellon' => 0.0,
            'clinicos' => 0.0,
            'valorizables' => 0.0,
            'examenes' => 0.0,
            'diaCama' => 0.0,
            'general' => 0.0,
        ];

        foreach ($budget->getDetails() as $detail) {
            $group = $this->resolveGroupForDetail($detail);
            if ($group === null) {
                continue;
            }

            $groupedDetails[$group][] = $detail;
            $amount = (float) ($detail->getAmount() ?? 0);
            $totals[$group] += $amount;
            $totals['general'] += $amount;
        }

        $observations = $this->budgetRepository->getEntityManager()
            ->getRepository(BudgetObservation::class)
            ->createQueryBuilder('bo')
            ->leftJoin('bo.member', 'member')
            ->addSelect('member')
            ->where('bo.budget = :budget')
            ->setParameter('budget', $budget)
            ->orderBy('bo.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $now = new \DateTimeImmutable();
        $isExpired = $budget->getExpiresAt() !== null && $budget->getExpiresAt() < $now;

        return $this->render('budget/detail/show.html.twig', [
            'budget' => $budget,
            'groupedDetails' => $groupedDetails,
            'totals' => $totals,
            'observations' => $observations,
            'isExpired' => $isExpired,
        ]);
    }

    private function resolveGroupForDetail(BudgetDetail $detail): ?string
    {
        return match ($detail->getItemType()) {
            'honorario', 'honorario_cama' => 'honorarios',
            'pabellon' => 'pabellon',
            'clinico', 'clinico_cama', 'clinico_manual' => 'clinicos',
            'valorizable' => 'valorizables',
            'examen' => 'examenes',
            'dia_cama' => 'diaCama',
            default => null,
        };
    }
}
