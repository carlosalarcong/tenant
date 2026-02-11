<?php

namespace App\Form\Maintainers\Hospital;

use App\Entity\Tenant\PhysicalExamBaseField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhysicalExamBaseFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => [
                    'placeholder' => 'Nombre del campo base'
                ]
            ])
            ->add('description', TextType::class, [
                'label' => 'Descripcion',
                'required' => false
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Orden',
                'attr' => [
                    'min' => 0
                ]
            ])
            ->add('fieldType', ChoiceType::class, [
                'label' => 'Tipo Campo',
                'required' => false,
                'choices' => [
                    'Texto' => 'text',
                    'Numero' => 'number',
                    'Select' => 'select'
                ]
            ])
            ->add('isRequired', CheckboxType::class, [
                'label' => 'Obligatorio',
                'required' => false
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PhysicalExamBaseField::class,
        ]);
    }
}
