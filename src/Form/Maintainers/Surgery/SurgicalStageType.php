<?php

namespace App\Form\Maintainers\Surgery;

use App\Entity\Tenant\Branch;
use App\Entity\Tenant\SurgicalStage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurgicalStageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Orden',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('abbreviation', TextType::class, [
                'label' => 'Abreviación',
                'required' => false,
                'attr' => ['class' => 'form-control', 'maxlength' => 255],
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => ['class' => 'form-control', 'maxlength' => 255],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4, 'maxlength' => 2000],
            ])
            ->add('isMandatory', CheckboxType::class, [
                'label' => 'Obligatorio',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('requiresLogin', CheckboxType::class, [
                'label' => 'Requiere Login',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('isSequential', CheckboxType::class, [
                'label' => 'Secuencial',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('branch', EntityType::class, [
                'class' => Branch::class,
                'choice_label' => 'name',
                'label' => 'Sucursal',
                'required' => false,
                'placeholder' => 'Seleccione una sucursal',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SurgicalStage::class,
        ]);
    }
}
