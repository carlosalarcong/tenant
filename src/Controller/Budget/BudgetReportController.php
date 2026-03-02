<?php

namespace App\Controller\Budget;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\Budget;
use App\Entity\Tenant\Branch;
use App\Entity\Tenant\Member;
use App\Repository\Tenant\BranchRepository;
use App\Repository\Tenant\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Export\ExportService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/budget', name: 'budget_')]
class BudgetReportController extends AbstractTenantAwareController
{
    public function __construct(
        private readonly BranchRepository $branchRepository,
        private readonly MemberRepository $memberRepository,
        private readonly ExportService $exportService,
        #[Autowire(service: 'doctrine.orm.tenant_entity_manager')]
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('/report', name: 'report', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $filters = [
            'dateFrom' => trim((string) $request->get('date_from', '')),
            'dateTo' => trim((string) $request->get('date_to', '')),
            'branchId' => (int) $request->get('branch_id', 0),
            'memberId' => (int) $request->get('member_id', 0),
        ];

        $rows = [];
        $totals = [
            'count' => 0,
            'amount' => 0.0,
        ];

        if ($request->isMethod('POST')) {
            [$rows, $totals] = $this->runReport($filters);
            $rows = $this->prepareReportRows($rows);
        }

        $members = $this->em->getRepository(Member::class)
            ->createQueryBuilder('m')
            ->where('m.isActive = true')
            ->orderBy('m.firstName', 'ASC')
            ->addOrderBy('m.lastName', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('budget/report/index.html.twig', [
            'filters' => $filters,
            'branches' => $this->branchRepository->findAllActive(),
            'members' => $members,
            'rows' => $rows,
            'totals' => $totals,
            'submitted' => $request->isMethod('POST'),
        ]);
    }

    #[Route('/report/export', name: 'report_export', methods: ['POST'])]
    public function export(Request $request): Response
    {
        $filters = [
            'dateFrom' => trim((string) $request->get('date_from', '')),
            'dateTo' => trim((string) $request->get('date_to', '')),
            'branchId' => (int) $request->get('branch_id', 0),
            'memberId' => (int) $request->get('member_id', 0),
        ];

        [$rows] = $this->runReport($filters);
        $rows = $this->prepareReportRows($rows);

        return $this->exportService->exportArrayToCsv(
            data: $rows,
            columns: [
                'number',
                'createdAt',
                'personName',
                'personLastName',
                'identification',
                'payerName',
                'agreementName',
                'insurancePlanName',
                'surgeryPackagePlanName',
                'professionalName',
                'totalAmount',
                'status',
            ],
            headers: [
                'N° Presupuesto',
                'Fecha',
                'Nombre',
                'Apellido',
                'RUT/Doc',
                'Financiador',
                'Convenio',
                'Plan Abierto',
                'Plan Paquetizado',
                'Profesional',
                'Total',
                'Estado',
            ],
            filename: 'presupuestos_' . (new \DateTime())->format('Y-m-d') . '.csv',
        );
    }

    /**
     * @param array{dateFrom:string,dateTo:string,branchId:int,memberId:int} $filters
     * @return array{0:list<array<string,mixed>>,1:array{count:int,amount:float}}
     */
    private function runReport(array $filters): array
    {
        $qb = $this->em->getRepository(Budget::class)
            ->createQueryBuilder('b')
            ->select(
                'b.id AS id',
                'b.number AS number',
                'b.createdAt AS createdAt',
                'b.status AS status',
                'person.name AS personName',
                'person.middleName AS personMiddleName',
                'person.lastName AS personLastName',
                'person.identification AS identification',
                'payer.name AS payerName',
                'agreement.name AS agreementName',
                'insurancePlan.name AS insurancePlanName',
                'surgeryPackagePlan.name AS surgeryPackagePlanName',
                "CONCAT(COALESCE(professional.firstName, ''), ' ', COALESCE(professional.lastName, '')) AS professionalName",
                'COALESCE(SUM(detail.amount), 0) AS totalAmount'
            )
            ->leftJoin('b.person', 'person')
            ->leftJoin('b.payer', 'payer')
            ->leftJoin('b.agreement', 'agreement')
            ->leftJoin('b.insurancePlan', 'insurancePlan')
            ->leftJoin('b.surgeryPackagePlan', 'surgeryPackagePlan')
            ->leftJoin('b.professional', 'professional')
            ->leftJoin('b.details', 'detail')
            ->groupBy('b.id, person.id, payer.id, agreement.id, insurancePlan.id, surgeryPackagePlan.id, professional.id')
            ->orderBy('b.createdAt', 'DESC');

        if ($filters['dateFrom'] !== '') {
            $dateFrom = \DateTimeImmutable::createFromFormat('Y-m-d', $filters['dateFrom']);
            if ($dateFrom instanceof \DateTimeImmutable) {
                $qb->andWhere('b.createdAt >= :dateFrom')
                    ->setParameter('dateFrom', $dateFrom->setTime(0, 0, 0));
            }
        }

        if ($filters['dateTo'] !== '') {
            $dateTo = \DateTimeImmutable::createFromFormat('Y-m-d', $filters['dateTo']);
            if ($dateTo instanceof \DateTimeImmutable) {
                $qb->andWhere('b.createdAt <= :dateTo')
                    ->setParameter('dateTo', $dateTo->setTime(23, 59, 59));
            }
        }

        if ($filters['branchId'] > 0) {
            $branch = $this->branchRepository->find($filters['branchId']);
            if ($branch instanceof Branch) {
                $qb->andWhere('b.branch = :branch')->setParameter('branch', $branch);
            }
        }

        if ($filters['memberId'] > 0) {
            $member = $this->memberRepository->find($filters['memberId']);
            if ($member instanceof Member) {
                $qb->andWhere('b.member = :member')->setParameter('member', $member);
            }
        }

        /** @var list<array<string,mixed>> $rows */
        $rows = $qb->getQuery()->getArrayResult();

        $totals = [
            'count' => count($rows),
            'amount' => array_sum(array_map(static fn (array $row): float => (float) $row['totalAmount'], $rows)),
        ];

        return [$rows, $totals];
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    private function prepareReportRows(array $rows): array
    {
        return array_map(static function (array $row): array {
            $createdAt = $row['createdAt'] ?? null;
            if ($createdAt instanceof \DateTimeInterface) {
                $row['createdAt'] = $createdAt->format('d/m/Y');
            } elseif (is_string($createdAt) && $createdAt !== '') {
                try {
                    $row['createdAt'] = (new \DateTimeImmutable($createdAt))->format('d/m/Y');
                } catch (\Throwable) {
                    $row['createdAt'] = $createdAt;
                }
            } else {
                $row['createdAt'] = '';
            }

            $row['totalAmount'] = number_format((float) ($row['totalAmount'] ?? 0), 0, ',', '.');
            $row['status'] = match ((string) ($row['status'] ?? '')) {
                'active' => 'Activo',
                'cancelled' => 'Anulado',
                default => ucfirst((string) ($row['status'] ?? '')),
            };

            return $row;
        }, $rows);
    }
}
