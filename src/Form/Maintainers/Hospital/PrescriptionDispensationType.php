<?php

namespace App\Form\Maintainers\Hospital;

use App\Entity\Tenant\PrescriptionDispensation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrescriptionDispensationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Orden',
                'attr' => [
                    'min' => 0,
                    'class' => 'form-control'
                ]
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Cantidad',
                'attr' => [
                    'min' => 1,
                    'class' => 'form-control'
                ]
            ])
            ->add('timeUnit', TextType::class, [
                'label' => 'Unidad de Tiempo',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ej: horas, dias',
                    'class' => 'form-control'
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
            'data_class' => PrescriptionDispensation::class,
        ]);
    }
}
