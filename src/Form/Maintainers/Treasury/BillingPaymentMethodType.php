<?php

namespace App\Form\Maintainers\Treasury;

use App\Entity\Tenant\BillingPaymentMethod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BillingPaymentMethodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Codigo',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ej: EFE',
                    'class' => 'form-control',
                    'maxlength' => 3
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ingrese nombre',
                    'class' => 'form-control'
                ]
            ])
            ->add('isCash', CheckboxType::class, [
                'label' => 'Es Efectivo',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BillingPaymentMethod::class,
        ]);
    }
}
