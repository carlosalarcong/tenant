<?php

namespace App\Controller\Nursing;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\AdmissionRecord;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nursing', name: 'app_nursing_')]
class NursingPrintController extends AbstractTenantAwareController
{
    public function __construct(private TenantEntityManager $entityManager) {}

    #[Route('/admission/{id}/brazalete', name: 'print_brazalete', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function brazalete(int $id): Response
    {
        $admission = $this->entityManager->find(AdmissionRecord::class, $id);
        if (!$admission instanceof AdmissionRecord) {
            throw $this->createNotFoundException('Admisión no encontrada.');
        }

        return $this->render('nursing/print/brazalete.html.twig', [
            'admission' => $admission,
        ]);
    }
}
