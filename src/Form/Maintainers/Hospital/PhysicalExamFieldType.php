<?php

namespace App\Form\Maintainers\Hospital;

use App\Entity\Tenant\PhysicalExamField;
use App\Entity\Tenant\PhysicalExamGrouping;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhysicalExamFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'required' => false
            ])
            ->add('description', TextType::class, [
                'label' => 'Descripcion',
                'required' => false
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Orden'
            ])
            ->add('rangeMin', IntegerType::class, [
                'label' => 'Rango Min',
                'required' => false
            ])
            ->add('rangeMax', IntegerType::class, [
                'label' => 'Rango Max',
                'required' => false
            ])
            ->add('ageMin', IntegerType::class, [
                'label' => 'Edad Min',
                'required' => false
            ])
            ->add('ageMax', IntegerType::class, [
                'label' => 'Edad Max',
                'required' => false
            ])
            ->add('unit', TextType::class, [
                'label' => 'Unidad',
                'required' => false
            ])
            ->add('isWeight', CheckboxType::class, [
                'label' => 'Peso',
                'required' => false
            ])
            ->add('isHeight', CheckboxType::class, [
                'label' => 'Talla',
                'required' => false
            ])
            ->add('isBmi', CheckboxType::class, [
                'label' => 'IMC',
                'required' => false
            ])
            ->add('isTemperature', CheckboxType::class, [
                'label' => 'Temperatura',
                'required' => false
            ])
            ->add('isSystolic', CheckboxType::class, [
                'label' => 'Sistolica',
                'required' => false
            ])
            ->add('isDiastolic', CheckboxType::class, [
                'label' => 'Diastolica',
                'required' => false
            ])
            ->add('isSaturation', CheckboxType::class, [
                'label' => 'Saturacion',
                'required' => false
            ])
            ->add('isRespiratoryRate', CheckboxType::class, [
                'label' => 'Freq Resp',
                'required' => false
            ])
            ->add('isPce', CheckboxType::class, [
                'label' => 'PCE',
                'required' => false
            ])
            ->add('grouping1', EntityType::class, [
                'class' => PhysicalExamGrouping::class,
                'label' => 'Agrupacion 1',
                'choice_label' => 'name',
                'placeholder' => 'Sin agrupacion',
                'required' => false,
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('g')
                        ->where('g.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('g.name', 'ASC');
                }
            ])
            ->add('grouping2', EntityType::class, [
                'class' => PhysicalExamGrouping::class,
                'label' => 'Agrupacion 2',
                'choice_label' => 'name',
                'placeholder' => 'Sin agrupacion',
                'required' => false,
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('g')
                        ->where('g.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('g.name', 'ASC');
                }
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
            'data_class' => PhysicalExamField::class,
        ]);
    }
}
