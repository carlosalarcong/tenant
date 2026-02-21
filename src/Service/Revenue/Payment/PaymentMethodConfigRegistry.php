<?php

namespace App\Service\Revenue\Payment;

use App\Form\Revenue\Payment\Method\CashPaymentType;
use App\Form\Revenue\Payment\Method\CheckPaymentType;
use App\Form\Revenue\Payment\Method\CreditCardPaymentType;

class PaymentMethodConfigRegistry
{
    /**
     * MVP solo incluye cash, credit_card y check.
     * Gratuity queda fuera por ahora porque no existe la entidad GratuityCause en tenant.
     *
     * @var array<string, array{label:string,form_type:class-string,row_template:string,max_rows:int}>
     */
    private array $config = [
        'cash' => [
            'label' => 'Efectivo',
            'form_type' => CashPaymentType::class,
            'row_template' => 'revenue/payment/method/_cash_row.html.twig',
            'max_rows' => 20,
        ],
        'credit_card' => [
            'label' => 'Tarjeta de Crédito',
            'form_type' => CreditCardPaymentType::class,
            'row_template' => 'revenue/payment/method/_credit_card_row.html.twig',
            'max_rows' => 20,
        ],
        'check' => [
            'label' => 'Cheque',
            'form_type' => CheckPaymentType::class,
            'row_template' => 'revenue/payment/method/_check_row.html.twig',
            'max_rows' => 20,
        ],
    ];

    public function has(string $methodCode): bool
    {
        return isset($this->config[$methodCode]);
    }

    /**
     * @return array{label:string,form_type:class-string,row_template:string,max_rows:int}
     */
    public function get(string $methodCode): array
    {
        if (!$this->has($methodCode)) {
            throw new \InvalidArgumentException(sprintf('Método de pago no soportado: %s', $methodCode));
        }

        return $this->config[$methodCode];
    }

    /**
     * @return array<string, array{label:string,form_type:class-string,row_template:string,max_rows:int}>
     */
    public function all(): array
    {
        return $this->config;
    }
}
