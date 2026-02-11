<?php

namespace App\Form\Maintainers\Logistics;

use App\Entity\Tenant\Specialty;
use App\Entity\Tenant\Warehouse;
use App\Entity\Tenant\WarehouseSpecialty;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WarehouseSpecialtyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('warehouse', EntityType::class, [
                'class' => Warehouse::class,
                'label' => 'Bodega',
                'choice_label' => 'name',
                'placeholder' => 'Seleccione una bodega...',
                'required' => true,
                'attr' => [
                    'class' => 'form-select'
                ],
                'query_builder' => function($repository) {
                    return $repository->createQueryBuilder('w')
                        ->where('w.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('w.name', 'ASC');
                }
            ])
            ->add('specialty', EntityType::class, [
                'class' => Specialty::class,
                'label' => 'Especialidad',
                'choice_label' => 'name',
                'placeholder' => 'Seleccione una especialidad...',
                'required' => true,
                'attr' => [
                    'class' => 'form-select'
                ],
                'query_builder' => function($repository) {
                    return $repository->createQueryBuilder('s')
                        ->where('s.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('s.name', 'ASC');
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
            'data_class' => WarehouseSpecialty::class,
        ]);
    }
}
