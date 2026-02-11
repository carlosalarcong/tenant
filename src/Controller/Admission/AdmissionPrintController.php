<?php

namespace App\Controller\Admission;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\AdmissionRecord;
use App\Service\Admission\AdmissionService;
use App\Service\Admission\AdmissionPrintService;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admission/print', name: 'app_admission_print_')]
class AdmissionPrintController extends AbstractTenantAwareController
{
    public function __construct(
        private TenantEntityManager $entityManager,
        private AdmissionService $admissionService,
        private AdmissionPrintService $admissionPrintService
    ) {}

    #[Route('/{id}/{type}', name: 'admission', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function admission(int $id, string $type): Response
    {
        if ($id <= 0) {
            throw $this->createNotFoundException('ID de admisión inválido');
        }

        if (!$this->admissionPrintService->isValidDocumentType($type)) {
            return new Response(
                'Tipo de documento inválido. Valores permitidos: ' . implode(', ', AdmissionPrintService::VALID_TYPES),
                400
            );
        }

        /** @var AdmissionRecord|null $record */
        $record = $this->entityManager->find(AdmissionRecord::class, $id);
        if (!$record instanceof AdmissionRecord) {
            throw $this->createNotFoundException('Admisión no encontrada');
        }

        $lookups = $this->admissionService->resolveAdmissionLookups($record);

        return $this->render('admission/print/admission_pdf.html.twig', array_merge(
            $this->admissionPrintService->buildAdmissionPrintContext($record, $type),
            [
            'admission_record' => $record,
            'admission_lookups' => $lookups,
            ]
        ));
    }

    #[Route('/urgency/{id}/{type}', name: 'urgency', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function urgency(int $id, string $type): Response
    {
        if ($id <= 0) {
            throw $this->createNotFoundException('ID de admisión inválido');
        }

        if (!$this->admissionPrintService->isValidDocumentType($type)) {
            return new Response(
                'Tipo de documento inválido. Valores permitidos: ' . implode(', ', AdmissionPrintService::VALID_TYPES),
                400
            );
        }

        /** @var AdmissionRecord|null $record */
        $record = $this->entityManager->find(AdmissionRecord::class, $id);
        if (!$record instanceof AdmissionRecord || $record->getAdmissionType() !== 'urgencia') {
            throw $this->createNotFoundException('Admisión urgencia no encontrada');
        }

        $lookups = $this->admissionService->resolveAdmissionLookups($record);

        return $this->render('admission/print/urgency_pdf.html.twig', array_merge(
            $this->admissionPrintService->buildAdmissionPrintContext($record, $type),
            [
            'admission_record' => $record,
            'admission_lookups' => $lookups,
            ]
        ));
    }
}
