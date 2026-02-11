<?php

namespace App\Form\Maintainers\Treasury;

use App\Entity\Tenant\PaymentCondition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentConditionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ingrese condicion de pago',
                    'class' => 'form-control'
                ]
            ])
            ->add('interfaceCode', TextType::class, [
                'label' => 'Codigo Interfaz',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Codigo',
                    'class' => 'form-control',
                    'maxlength' => 10
                ]
            ])
            ->add('maxTerm', IntegerType::class, [
                'label' => 'Plazo Maximo (dias)',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ]
            ])
            ->add('isUpToDate', CheckboxType::class, [
                'label' => 'Es Al Dia',
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
            'data_class' => PaymentCondition::class,
        ]);
    }
}
