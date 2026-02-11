<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\TreatmentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class TreatmentTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ingrese el nombre del tipo de tratamiento',
                    'maxlength' => 100
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'El nombre es obligatorio']),
                    new Assert\Length([
                        'max' => 100,
                        'maxMessage' => 'El nombre no puede exceder {{ limit }} caracteres'
                    ])
                ]
            ])
            ->add('code', TextType::class, [
                'label' => 'Código',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Código único (opcional)',
                    'maxlength' => 20
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 20,
                        'maxMessage' => 'El código no puede exceder {{ limit }} caracteres'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Descripción opcional',
                    'rows' => 3
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
            'data_class' => TreatmentType::class,
        ]);
    }
}
