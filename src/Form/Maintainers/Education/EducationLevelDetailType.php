<?php

namespace App\Form\Maintainers\Education;

use App\Entity\Tenant\EducationLevel;
use App\Entity\Tenant\EducationLevelDetail;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EducationLevelDetailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => [
                    'placeholder' => 'Ingrese el detalle del nivel',
                    'class' => 'form-control'
                ]
            ])
            ->add('educationLevel', EntityType::class, [
                'class' => EducationLevel::class,
                'choice_label' => 'name',
                'label' => 'Nivel de Instrucción',
                'placeholder' => 'Seleccione un nivel',
                'attr' => [
                    'class' => 'form-select'
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
            'data_class' => EducationLevelDetail::class,
        ]);
    }
}
