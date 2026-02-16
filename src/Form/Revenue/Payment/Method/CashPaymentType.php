<?php

namespace App\Form\Revenue\Payment\Method;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CashPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('amount', NumberType::class, [
            'required' => true,
            'scale' => 2,
            'html5' => true,
            'attr' => [
                'step' => '0.01',
                'min' => '0',
                'data-payment-amount-input' => '1',
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
