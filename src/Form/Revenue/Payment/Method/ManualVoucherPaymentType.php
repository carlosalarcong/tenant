<?php

namespace App\Form\Revenue\Payment\Method;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Bono Manual — bono en papel emitido por la aseguradora y entregado físicamente.
 * Legacy: FormaDePago_BonoManual.html.twig (tipo 5)
 */
class ManualVoucherPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('folio', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => 20,
                    'placeholder' => 'N° folio bono',
                ],
            ])
            ->add('payer_name', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => 100,
                    'placeholder' => 'Nombre del financiador',
                ],
            ])
            ->add('amount', NumberType::class, [
                'required' => true,
                'scale' => 2,
                'html5' => true,
                'attr' => [
                    'step' => '0.01',
                    'min' => '0',
                    'data-payment-amount-input' => '1',
                    'placeholder' => 'Ej: 15000',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
