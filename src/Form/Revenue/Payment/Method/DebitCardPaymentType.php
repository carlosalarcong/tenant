<?php

namespace App\Form\Revenue\Payment\Method;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DebitCardPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('voucher', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => 12,
                    'placeholder' => 'N° voucher',
                ],
            ])
            ->add('card_last_digits', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => 4,
                    'placeholder' => '1234',
                    'pattern' => '\d{4}',
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
                    'placeholder' => 'Ej: 150000',
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
