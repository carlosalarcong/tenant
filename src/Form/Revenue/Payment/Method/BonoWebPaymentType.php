<?php

namespace App\Form\Revenue\Payment\Method;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * BonoWebPaymentType
 *
 * Fila de formulario para el medio de pago BonoWeb (bono FONASA electrónico).
 *
 * Campos:
 *   - amount     → copago del paciente (se copia desde BonoWebVoucher.copagoTotal)
 *   - voucher_id → UUID del voucher Snabb (se copia desde BonoWebVoucher.voucherId)
 */
class BonoWebPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', NumberType::class, [
                'label'    => 'Copago paciente',
                'required' => true,
                'scale'    => 2,
                'html5'    => true,
                'attr'     => [
                    'step'                    => '0.01',
                    'min'                     => '0',
                    'data-payment-amount-input' => '1',
                    'readonly'                => true,
                    'class'                   => 'bg-light',
                ],
            ])
            ->add('voucher_id', TextType::class, [
                'label'    => 'ID Voucher BonoWeb',
                'required' => true,
                'attr'     => [
                    'readonly'    => true,
                    'class'       => 'font-monospace bg-light',
                    'placeholder' => 'UUID del voucher (auto-completado)',
                ],
                'help' => 'Se completa automáticamente al confirmar el bono en el panel BonoWeb.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
