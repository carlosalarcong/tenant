<?php

namespace App\Controller\Revenue\Payment;

use App\Controller\AbstractTenantAwareController;
use App\Service\Revenue\Payment\PaymentMethodConfigRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/revenue/payment', name: 'app_revenue_payment_')]
class RevenuePaymentFragmentController extends AbstractTenantAwareController
{
    public function __construct(
        private readonly PaymentMethodConfigRegistry $configRegistry,
        private readonly FormFactoryInterface $formFactory
    ) {}

    #[Route('/row/{methodCode}', name: 'row', methods: ['GET'])]
    public function row(Request $request, string $methodCode): Response
    {
        if (!$this->configRegistry->has($methodCode)) {
            throw $this->createNotFoundException(sprintf('Método de pago no soportado: %s', $methodCode));
        }

        $index = max(0, (int) $request->query->get('index', 0));
        $config = $this->configRegistry->get($methodCode);

        if ($index > $config['max_rows']) {
            throw $this->createNotFoundException('Se superó la cantidad máxima de filas permitidas para este método.');
        }

        // Symfony no permite [] en el nombre interno del form; se fuerza full_name en Twig.
        $form = $this->formFactory->createNamed(
            sprintf('payment_batch_rows_%s_%d', $methodCode, $index),
            $config['form_type'],
            null,
            [
                'csrf_protection' => false,
            ]
        );

        return $this->render($config['row_template'], [
            'row_form' => $form->createView(),
            'method_code' => $methodCode,
            'index' => $index,
            'row_name_prefix' => sprintf('payment_batch[rows][%s][%d]', $methodCode, $index),
        ]);
    }
}
