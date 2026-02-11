<?php

namespace App\Form\Maintainers\Logistics;

use App\Entity\Tenant\DispatchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DispatchTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Código',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ingrese el código (opcional)',
                    'class' => 'form-control',
                    'maxlength' => 50
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => [
                    'placeholder' => 'Ingrese el nombre del tipo de despacho',
                    'class' => 'form-control',
                    'maxlength' => 100
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
            'data_class' => DispatchType::class,
        ]);
    }
}
