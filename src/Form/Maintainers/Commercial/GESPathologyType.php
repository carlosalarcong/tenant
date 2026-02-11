<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\GESPathology;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class GESPathologyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pathologyNumber', TextType::class, [
                'label' => 'Número de Patología',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ej: 01, 02, ...', 'maxlength' => 10]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nombre de la patología GES', 'maxlength' => 200],
                'constraints' => [new Assert\NotBlank(['message' => 'El nombre es obligatorio'])]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Descripción de la patología']
            ])
            ->add('minAge', IntegerType::class, [
                'label' => 'Edad Mínima (años)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Edad mínima en años']
            ])
            ->add('maxAge', IntegerType::class, [
                'label' => 'Edad Máxima (años)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Edad máxima en años']
            ])
            ->add('minAgeMonths', IntegerType::class, [
                'label' => 'Edad Mínima (meses)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Para niños pequeños']
            ])
            ->add('maxAgeMonths', IntegerType::class, [
                'label' => 'Edad Máxima (meses)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Para niños pequeños']
            ])
            ->add('genderRestriction', ChoiceType::class, [
                'label' => 'Restricción de Género',
                'choices' => [
                    'Sin Restricción' => null,
                    'Solo Masculino' => 'male',
                    'Solo Femenino' => 'female'
                ],
                'placeholder' => 'Seleccione una opción',
                'required' => false,
                'attr' => ['class' => 'form-select']
            ])
            ->add('guaranteedDays', IntegerType::class, [
                'label' => 'Días de Garantía',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Días garantizados para atención']
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GESPathology::class,
        ]);
    }
}
