<?php

namespace App\Form\Maintainers\Organizational;

use App\Entity\Tenant\Branch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BranchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ingrese el nombre de la sucursal',
                    'class' => 'form-control',
                    'maxlength' => 255
                ]
            ])
            ->add('code', TextType::class, [
                'label' => 'Código',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Código identificador (opcional)',
                    'class' => 'form-control',
                    'maxlength' => 100
                ]
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Dirección',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Dirección completa de la sucursal',
                    'class' => 'form-control',
                    'rows' => 2
                ]
            ])
            ->add('phone', TextType::class, [
                'label' => 'Teléfono',
                'required' => false,
                'attr' => [
                    'placeholder' => '+56 9 1234 5678',
                    'class' => 'form-control',
                    'maxlength' => 50
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
                'attr' => [
                    'placeholder' => 'sucursal@empresa.com',
                    'class' => 'form-control',
                    'maxlength' => 255
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'Ciudad',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ciudad',
                    'class' => 'form-control',
                    'maxlength' => 100
                ]
            ])
            ->add('region', TextType::class, [
                'label' => 'Región',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Región',
                    'class' => 'form-control',
                    'maxlength' => 100
                ]
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Código Postal',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Código postal',
                    'class' => 'form-control',
                    'maxlength' => 20
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
            'data_class' => Branch::class,
        ]);
    }
}
