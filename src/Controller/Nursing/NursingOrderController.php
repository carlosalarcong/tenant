<?php

namespace App\Controller\Nursing;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\AdmissionRecord;
use App\Entity\Tenant\NursingOrder;
use App\Entity\Tenant\Person;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nursing', name: 'app_nursing_')]
class NursingOrderController extends AbstractTenantAwareController
{
    public function __construct(private TenantEntityManager $entityManager) {}

    #[Route('/patient/{id}/order/create', name: 'patient_order_create', methods: ['GET', 'POST'], requirements: ['id' => '\\d+'])]
    public function create(int $id, Request $request): Response
    {
        $record = $this->entityManager->find(AdmissionRecord::class, $id);
        if (!$record instanceof AdmissionRecord) {
            throw $this->createNotFoundException('Admisión no encontrada.');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('nursing_order_create', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }
            $orderedBy = $record->getPerson();
            $orderedById = (int) $request->request->get('orderedById', 0);
            $candidate = $this->entityManager->find(Person::class, $orderedById);
            if ($candidate instanceof Person) {
                $orderedBy = $candidate;
            }

            $order = (new NursingOrder())
                ->setAdmissionRecord($record)
                ->setOrderedBy($orderedBy)
                ->setOrderType((string) $request->request->get('orderType', 'procedimiento'))
                ->setDescription((string) $request->request->get('description', ''))
                ->setStatus('active');

            $this->entityManager->persist($order);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_nursing_patient_orders', ['id' => $id]);
        }

        return $this->render('nursing/patient/forms/order_form.html.twig', [
            'admission_id' => $id,
        ]);
    }

    #[Route('/patient/{id}/order/{oid}/edit', name: 'patient_order_edit', methods: ['GET', 'POST'], requirements: ['id' => '\\d+', 'oid' => '\\d+'])]
    public function edit(int $id, int $oid, Request $request): Response
    {
        $order = $this->entityManager->find(NursingOrder::class, $oid);
        if (!$order instanceof NursingOrder) {
            throw $this->createNotFoundException('Indicación no encontrada.');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('nursing_order_edit', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }
            $order->setOrderType((string) $request->request->get('orderType', $order->getOrderType()));
            $order->setDescription((string) $request->request->get('description', $order->getDescription()));
            $order->setStatus((string) $request->request->get('status', $order->getStatus()));
            $this->entityManager->flush();

            return $this->redirectToRoute('app_nursing_patient_orders', ['id' => $id]);
        }

        return $this->render('nursing/patient/forms/order_edit_form.html.twig', [
            'admission_id' => $id,
            'order' => $order,
        ]);
    }

    #[Route('/patient/{id}/order/{oid}/cancel', name: 'patient_order_cancel', methods: ['POST'], requirements: ['id' => '\\d+', 'oid' => '\\d+'])]
    public function cancel(int $id, int $oid, Request $request): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('nursing_order_cancel', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $order = $this->entityManager->find(NursingOrder::class, $oid);
        if ($order instanceof NursingOrder) {
            $order->setStatus('cancelled');
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_nursing_patient_orders', ['id' => $id]);
    }
}
