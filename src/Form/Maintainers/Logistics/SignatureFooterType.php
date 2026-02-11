<?php

namespace App\Form\Maintainers\Logistics;

use App\Entity\Tenant\Branch;
use App\Entity\Tenant\SignatureFooter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignatureFooterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', IntegerType::class, [
                'label' => 'Código',
                'attr' => [
                    'placeholder' => 'Ingrese el código',
                    'class' => 'form-control'
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => [
                    'placeholder' => 'Nombre del firmante',
                    'class' => 'form-control',
                    'maxlength' => 255
                ]
            ])
            ->add('position', TextType::class, [
                'label' => 'Cargo',
                'attr' => [
                    'placeholder' => 'Cargo del firmante',
                    'class' => 'form-control',
                    'maxlength' => 255
                ]
            ])
            ->add('branch', EntityType::class, [
                'class' => Branch::class,
                'label' => 'Sucursal',
                'choice_label' => 'name',
                'placeholder' => 'Seleccione una sucursal...',
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ],
                'query_builder' => function($repository) {
                    return $repository->createQueryBuilder('b')
                        ->where('b.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('b.name', 'ASC');
                }
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
            'data_class' => SignatureFooter::class,
        ]);
    }
}
