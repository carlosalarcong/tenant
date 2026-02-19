<?php

namespace App\Controller\Nursing;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\AdmissionRecord;
use App\Repository\Tenant\NursingDischargeRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nursing', name: 'app_nursing_')]
class NursingPrintController extends AbstractTenantAwareController
{
    public function __construct(
        private TenantEntityManager $entityManager,
        private NursingDischargeRepository $nursingDischargeRepository
    ) {}

    #[Route('/print/{id}/discharge', name: 'print_discharge', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function discharge(int $id): Response
    {
        $record = $this->entityManager->find(AdmissionRecord::class, $id);
        if (!$record instanceof AdmissionRecord) {
            throw $this->createNotFoundException('Admisión no encontrada.');
        }

        $discharges = $this->nursingDischargeRepository->findBy(['admissionRecord' => $record], ['dischargedAt' => 'DESC']);

        return $this->render('nursing/print/discharge.html.twig', [
            'admission_record' => $record,
            'discharge' => $discharges[0] ?? null,
        ]);
    }
}
