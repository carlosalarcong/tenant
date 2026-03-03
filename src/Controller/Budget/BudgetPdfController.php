<?php

namespace App\Controller\Budget;

use App\Entity\Tenant\BudgetObservation;
use App\Entity\Tenant\Member;
use App\Repository\Tenant\BudgetRepository;
use App\Service\Revenue\PdfService;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/budget')]
class BudgetPdfController extends AbstractController
{
    public function __construct(
        private BudgetRepository $budgetRepository,
        private PdfService $pdfService,
        private MailerInterface $mailer,
        private TenantEntityManager $tenantEntityManager,
    ) {}

    #[Route('/{id}/pdf', name: 'app_budget_pdf', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function pdf(int $id): Response
    {
        $budget = $this->budgetRepository->find($id);
        if (!$budget) {
            throw $this->createNotFoundException('Presupuesto no encontrado.');
        }

        $content = $this->pdfService->generateBudgetPdf($budget, false);

        return new Response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="presupuesto-' . $budget->getNumber() . '.pdf"',
        ]);
    }

    #[Route('/{id}/pdf-summary', name: 'app_budget_pdf_summary', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function pdfSummary(int $id): Response
    {
        $budget = $this->budgetRepository->find($id);
        if (!$budget) {
            throw $this->createNotFoundException('Presupuesto no encontrado.');
        }

        $content = $this->pdfService->generateBudgetPdf($budget, true);

        return new Response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="resumen-presupuesto-' . $budget->getNumber() . '.pdf"',
        ]);
    }

    #[Route('/{id}/email', name: 'app_budget_email', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function sendEmail(int $id, Request $request): Response
    {
        $budget = $this->budgetRepository->find($id);
        if (!$budget) {
            throw $this->createNotFoundException('Presupuesto no encontrado.');
        }

        $overrideEmail  = trim($request->request->get('email', ''));
        $patientEmail   = $budget->getPerson()->getEmail();

        if (!$overrideEmail && !$patientEmail) {
            return $this->json(['error' => 'No hay email del paciente'], 400);
        }

        $to = $overrideEmail ?: $patientEmail;

        $pdfContent = $this->pdfService->generateBudgetPdf($budget, false);

        $email = (new Email())
            ->from('no-reply@clinica.cl')
            ->to($to)
            ->subject('Presupuesto N° ' . $budget->getNumber())
            ->text('Adjunto encontrará su presupuesto médico N° ' . $budget->getNumber() . '.')
            ->attach(
                $pdfContent,
                'presupuesto-' . $budget->getNumber() . '.pdf',
                'application/pdf'
            );

        $this->mailer->send($email);

        $obs = new BudgetObservation();
        $obs->setBudget($budget);
        $obs->setObservation('Presupuesto enviado por email a: ' . $to);
        $obs->setIsEmailSent(true);
        $user = $this->getUser();
        if ($user instanceof Member) {
            $obs->setMember($user);
        }
        $this->tenantEntityManager->persist($obs);
        $this->tenantEntityManager->flush();

        return $this->json(['success' => true, 'sentTo' => $to]);
    }
}
