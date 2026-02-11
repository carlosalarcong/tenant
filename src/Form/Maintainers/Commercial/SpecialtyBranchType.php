<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\Branch;
use App\Entity\Tenant\Specialty;
use App\Entity\Tenant\SpecialtyBranch;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SpecialtyBranchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('specialty', EntityType::class, [
                'label' => 'Especialidad',
                'class' => Specialty::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione una especialidad',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank(['message' => 'La especialidad es obligatoria'])],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('s')
                        ->where('s.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('s.name', 'ASC');
                }
            ])
            ->add('branch', EntityType::class, [
                'label' => 'Sucursal',
                'class' => Branch::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione una sucursal',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank(['message' => 'La sucursal es obligatoria'])],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('b')
                        ->where('b.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('b.name', 'ASC');
                }
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
            'data_class' => SpecialtyBranch::class,
        ]);
    }
}
