<?php

namespace App\Service\Revenue\Payment;

use App\Form\Revenue\Payment\Method\BankTransferPaymentType;
use App\Form\Revenue\Payment\Method\BonoWebPaymentType;
use App\Form\Revenue\Payment\Method\CashPaymentType;
use App\Form\Revenue\Payment\Method\CheckPaymentType;
use App\Form\Revenue\Payment\Method\CreditCardPaymentType;
use App\Form\Revenue\Payment\Method\DebitCardPaymentType;
use App\Form\Revenue\Payment\Method\ElectronicVoucherPaymentType;
use App\Form\Revenue\Payment\Method\GratuityPaymentType;
use App\Form\Revenue\Payment\Method\ManualVoucherPaymentType;

class PaymentMethodConfigRegistry
{
    /**
     * @var array<string, array{label:string,form_type:class-string,row_template:string,max_rows:int}>
     */
    private array $config = [
        // ── Pagos en efectivo ──────────────────────────────────────────────
        'cash' => [
            'label'        => 'Efectivo',
            'form_type'    => CashPaymentType::class,
            'row_template' => 'revenue/payment/method/_cash_row.html.twig',
            'max_rows'     => 20,
        ],

        // ── Tarjetas ───────────────────────────────────────────────────────
        'credit_card' => [
            'label'        => 'Tarjeta de Crédito',
            'form_type'    => CreditCardPaymentType::class,
            'row_template' => 'revenue/payment/method/_credit_card_row.html.twig',
            'max_rows'     => 20,
        ],
        'debit_card' => [
            'label'        => 'Tarjeta de Débito',
            'form_type'    => DebitCardPaymentType::class,
            'row_template' => 'revenue/payment/method/_debit_card_row.html.twig',
            'max_rows'     => 20,
        ],

        // ── Bonos ──────────────────────────────────────────────────────────
        'electronic_voucher' => [
            'label'        => 'Bono Electrónico',
            'form_type'    => ElectronicVoucherPaymentType::class,
            'row_template' => 'revenue/payment/method/_electronic_voucher_row.html.twig',
            'max_rows'     => 20,
        ],
        'manual_voucher' => [
            'label'        => 'Bono Manual',
            'form_type'    => ManualVoucherPaymentType::class,
            'row_template' => 'revenue/payment/method/_manual_voucher_row.html.twig',
            'max_rows'     => 20,
        ],
        'bonoweb' => [
            'label'        => 'BonoWeb FONASA',
            'form_type'    => BonoWebPaymentType::class,
            'row_template' => 'revenue/payment/method/_bonoweb_row.html.twig',
            'max_rows'     => 1,
        ],

        // ── Documentos bancarios ───────────────────────────────────────────
        'check' => [
            'label'        => 'Cheque',
            'form_type'    => CheckPaymentType::class,
            'row_template' => 'revenue/payment/method/_check_row.html.twig',
            'max_rows'     => 20,
        ],
        'bank_transfer' => [
            'label'        => 'Transferencia Bancaria',
            'form_type'    => BankTransferPaymentType::class,
            'row_template' => 'revenue/payment/method/_bank_transfer_row.html.twig',
            'max_rows'     => 20,
        ],

        // ── Exenciones ─────────────────────────────────────────────────────
        'gratuity' => [
            'label'        => 'Gratuidad',
            'form_type'    => GratuityPaymentType::class,
            'row_template' => 'revenue/payment/method/_gratuity_row.html.twig',
            'max_rows'     => 5,
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
