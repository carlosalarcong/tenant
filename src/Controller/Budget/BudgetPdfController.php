<?php

namespace App\Controller\Budget;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\Budget;
use App\Entity\Tenant\BudgetDetail;
use App\Entity\Tenant\BudgetObservation;
use App\Entity\Tenant\Member;
use App\Service\Revenue\PdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/budget', name: 'budget_')]
class BudgetPdfController extends AbstractTenantAwareController
{
    public function __construct(
        #[Autowire(service: 'doctrine.orm.tenant_entity_manager')]
        private readonly EntityManagerInterface $em,
        private readonly PdfService $pdfService,
        private readonly MailerInterface $mailer,
    ) {}

    #[Route('/{id}/pdf', name: 'pdf', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function pdf(int $id): Response
    {
        $budget = $this->findBudget($id);
        [$groupedDetails, $totals] = $this->buildBudgetBreakdown($budget);
        $pdf = $this->pdfService->generateBudgetPdf($budget, $groupedDetails, $totals);

        return new Response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="presupuesto-%d.pdf"', $budget->getNumber() ?? $budget->getId()),
        ]);
    }

    #[Route('/{id}/pdf-summary', name: 'pdf_summary', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function pdfSummary(int $id): Response
    {
        $budget = $this->findBudget($id);
        [$groupedDetails, $totals] = $this->buildBudgetBreakdown($budget);
        $pdf = $this->pdfService->generateBudgetSummaryPdf($budget, $groupedDetails, $totals);

        return new Response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="presupuesto-%d-resumen.pdf"', $budget->getNumber() ?? $budget->getId()),
        ]);
    }

    #[Route('/{id}/email', name: 'send_email', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function sendEmail(int $id, Request $request): Response
    {
        unset($request);

        $budget = $this->findBudget($id);
        $patientEmail = $budget->getPerson()?->getEmail();
        [$groupedDetails, $totals] = $this->buildBudgetBreakdown($budget);

        if (!$patientEmail) {
            $this->addFlash('error', 'El paciente no tiene email registrado.');

            return $this->renderBudgetTurboStream($budget);
        }

        $pdf = $this->pdfService->generateBudgetPdf($budget, $groupedDetails, $totals);
        $tempPath = sys_get_temp_dir() . '/budget_' . $budget->getId() . '.pdf';
        file_put_contents($tempPath, $pdf);

        try {
            $clinicName = $this->getTenantName() ?: ($_ENV['MAILER_SENDER_NAME'] ?? 'Clínica');
            $senderAddress = $_ENV['MAILER_SENDER_ADDRESS'] ?? 'no-reply@example.com';
            $senderName = $_ENV['MAILER_SENDER_NAME'] ?? $clinicName;

            $email = (new TemplatedEmail())
                ->from(new Address($senderAddress, $senderName))
                ->to($patientEmail)
                ->subject(sprintf('Presupuesto N° %d - %s', $budget->getNumber(), $clinicName))
                ->htmlTemplate('budget/email/budget_email.html.twig')
                ->context([
                    'budget' => $budget,
                    'clinicName' => $clinicName,
                    'patient' => $budget->getPerson(),
                ])
                ->attachFromPath($tempPath, sprintf('presupuesto-%d.pdf', $budget->getNumber()));

            try {
                $this->mailer->send($email);

                $observation = (new BudgetObservation())
                    ->setBudget($budget)
                    ->setMember($this->resolveCurrentMember())
                    ->setObservation('Envío de presupuesto por email')
                    ->setIsEmailSent(true)
                    ->setCreatedAt(new \DateTime());

                $this->em->persist($observation);
                $this->em->flush();

                $this->addFlash('success', 'Presupuesto enviado por email correctamente.');
            } catch (\Throwable) {
                $this->addFlash('error', 'No fue posible enviar el presupuesto por email.');
            }
        } finally {
            if (is_file($tempPath)) {
                unlink($tempPath);
            }
        }

        return $this->renderBudgetTurboStream($budget);
    }

    private function findBudget(int $id): Budget
    {
        $budget = $this->em->getRepository(Budget::class)
            ->createQueryBuilder('b')
            ->leftJoin('b.person', 'person')
            ->leftJoin('b.payer', 'payer')
            ->leftJoin('b.agreement', 'agreement')
            ->leftJoin('b.insurancePlan', 'insurancePlan')
            ->leftJoin('b.surgeryPackagePlan', 'surgeryPackagePlan')
            ->leftJoin('b.professional', 'professional')
            ->leftJoin('b.careType', 'careType')
            ->leftJoin('b.origin', 'origin')
            ->leftJoin('b.branch', 'branch')
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
                'branch',
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

        return $budget;
    }

    /**
     * @return array{0: array<string, list<BudgetDetail>>, 1: array<string, float>}
     */
    private function buildBudgetBreakdown(Budget $budget): array
    {
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

        return [$groupedDetails, $totals];
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

    private function renderBudgetTurboStream(Budget $budget): Response
    {
        $observations = $this->em->getRepository(BudgetObservation::class)
            ->createQueryBuilder('bo')
            ->leftJoin('bo.member', 'member')
            ->addSelect('member')
            ->where('bo.budget = :budget')
            ->setParameter('budget', $budget)
            ->orderBy('bo.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $flashHtml = $this->renderView('components/_flash_messages.html.twig');
        $observationsHtml = $this->renderView('budget/partials/_observations.html.twig', [
            'budget' => $budget,
            'observations' => $observations,
            'error' => null,
        ]);

        $stream = sprintf(
            '<turbo-stream action="update" target="flash-messages"><template>%s</template></turbo-stream><turbo-stream action="replace" target="budget-observations"><template>%s</template></turbo-stream>',
            $flashHtml,
            $observationsHtml
        );

        return new Response($stream, Response::HTTP_OK, [
            'Content-Type' => 'text/vnd.turbo-stream.html',
        ]);
    }

    private function resolveCurrentMember(): Member
    {
        $user = $this->getUser();
        if (!$user instanceof Member) {
            throw $this->createAccessDeniedException('Usuario no autenticado.');
        }

        return $user;
    }
}
