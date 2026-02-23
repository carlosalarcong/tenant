<?php

namespace App\Controller\Revenue\PatientAccount;

use App\Controller\AbstractTenantAwareController;
use App\Repository\Tenant\AdmissionRecordRepository;
use App\Repository\Tenant\PatientAccountRepository;
use App\Repository\Tenant\PatientRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * PatientAccount (PagoCuenta) endpoints.
 *
 * These are support fragments consumed by the CashRegister flow and by Admisión.
 * All HTML endpoints return Turbo Frame content — no full layout.
 *
 * Legacy source: RecaudacionBundle/Controller/Api/Unab/PagoCuenta/CuentaPacienteController
 */
#[Route('/revenue/patient-account', name: 'app_revenue_patient_account_')]
class PatientAccountController extends AbstractTenantAwareController
{
    /**
     * AccountStatus.code values that represent a pending or open debt.
     * Compared directly against AccountStatus::getCode() — no normalization needed.
     *
     * Legacy source: verificarPagosPendientesAction, EstadoCuenta constants.
     */
    private const PENDING_ACCOUNT_STATUS_CODES = [
        'cerrada_revision_interna',
        'cerrada_revision_financiador',
        'cerrada_pendiente_pago',
        'cerrada_cobranza_interna',
        'cerrada_cobranza_judicial',
        'cerrada_pagada_con_saldo_pendiente',
        'abierta_pendiente_pago',
    ];

    public function __construct(
        private readonly PatientAccountRepository $patientAccountRepository,
        private readonly AdmissionRecordRepository $admissionRecordRepository,
        private readonly PatientRepository $patientRepository,
    ) {}

    /**
     * Show the billing account summary for a patient (and any guardian-linked accounts).
     *
     * Turbo Frame: patient-account-summary
     * Legacy: mostrarCuentaAction
     *
     * GET /revenue/patient-account/summary?patientId={Patient.id}
     */
    #[Route('/summary', name: 'summary', methods: ['GET'])]
    public function summary(Request $request): Response
    {
        $patientId = $request->query->getInt('patientId');

        if ($patientId <= 0) {
            return $this->render('revenue/patient-account/_account_summary.html.twig', [
                'patient'         => null,
                'account'         => null,
                'guardianAccounts' => [],
            ]);
        }

        $patient = $this->patientRepository->find($patientId);
        if (!$patient) {
            throw $this->createNotFoundException('Paciente no encontrado.');
        }

        $account = $this->patientAccountRepository->findByPatientId($patientId);

        // If the patient has a legal guardian (tutor), also show accounts of other
        // patients for whom that same person acts as guardian.
        $guardianAccounts = [];
        if ($patient->getTutor() !== null) {
            $guardianAccounts = $this->patientAccountRepository
                ->findByTutorPersonId($patient->getTutor()->getId());
        }

        return $this->render('revenue/patient-account/_account_summary.html.twig', [
            'patient'          => $patient,
            'account'          => $account,
            'guardianAccounts' => $guardianAccounts,
        ]);
    }

    /**
     * Show the payer/financier context for a patient.
     * Used by the cashier to confirm which insurance plan applies before billing.
     *
     * Turbo Frame: patient-account-financier
     * Legacy: mostrarFinanciadorPacienteAction
     *
     * GET /revenue/patient-account/financier?patientId={Patient.id}
     */
    #[Route('/financier', name: 'financier', methods: ['GET'])]
    public function financier(Request $request): Response
    {
        $patientId = $request->query->getInt('patientId');

        if ($patientId <= 0) {
            return $this->render('revenue/patient-account/_financier_form.html.twig', [
                'patient' => null,
            ]);
        }

        $patient = $this->patientRepository->find($patientId);
        if (!$patient) {
            throw $this->createNotFoundException('Paciente no encontrado.');
        }

        return $this->render('revenue/patient-account/_financier_form.html.twig', [
            'patient' => $patient,
        ]);
    }

    /**
     * Show the financial guarantee detail for an admission (list of PaymentAccounts).
     *
     * Turbo Frame: patient-account-guarantee
     * Legacy: insertarReguardoFinancieroAction
     *
     * GET /revenue/patient-account/financial-guarantee?admissionRecordId={AdmissionRecord.id}
     */
    #[Route('/financial-guarantee', name: 'guarantee', methods: ['GET'])]
    public function financialGuarantee(Request $request): Response
    {
        $admissionRecordId = $request->query->getInt('admissionRecordId');

        if ($admissionRecordId <= 0) {
            return $this->render('revenue/patient-account/_financial_guarantee.html.twig', [
                'admission'      => null,
                'account'        => null,
                'paymentAccounts' => [],
            ]);
        }

        $admission = $this->admissionRecordRepository->findWithAccountById($admissionRecordId);
        if (!$admission) {
            throw $this->createNotFoundException('Registro de admisión no encontrado.');
        }

        $account = $admission->getPatientAccount();
        $paymentAccounts = $account !== null
            ? $account->getPaymentAccounts()->toArray()
            : [];

        return $this->render('revenue/patient-account/_financial_guarantee.html.twig', [
            'admission'       => $admission,
            'account'         => $account,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    /**
     * Check whether a patient has any open/pending payment accounts.
     * Includes admissions where the patient's person acts as legal guardian.
     * Pre-admission records are excluded (they are not real debts).
     *
     * Returns JSON: { "hasPending": true|false }
     *
     * Legacy: verificarPagosPendientesAction — changed from plain-text to JsonResponse.
     *
     * GET /revenue/patient-account/has-pending-payments?patientId={Patient.id}
     */
    #[Route('/has-pending-payments', name: 'pending', methods: ['GET'])]
    public function hasPendingPayments(Request $request): JsonResponse
    {
        $patientId = $request->query->getInt('patientId');

        if ($patientId <= 0) {
            return $this->json(['hasPending' => false]);
        }

        $patient = $this->patientRepository->find($patientId);
        if (!$patient) {
            return $this->json(['hasPending' => false]);
        }

        $personId = $patient->getPerson()->getId();
        $admissions = $this->admissionRecordRepository
            ->findForPaymentCheckByPersonId($personId);

        $hasPending = false;
        foreach ($admissions as $admission) {
            $account = $admission->getPatientAccount();
            if ($account === null) {
                continue;
            }
            $status = $account->getAccountStatus();
            if ($status === null) {
                continue;
            }
            $code = $status->getCode();
            if (in_array($code, self::PENDING_ACCOUNT_STATUS_CODES, true)) {
                $hasPending = true;
                break;
            }
        }

        return $this->json(['hasPending' => $hasPending]);
    }
}
