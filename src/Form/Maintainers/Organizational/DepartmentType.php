<?php

namespace App\Form\Maintainers\Organizational;

use App\Entity\Tenant\Branch;
use App\Entity\Tenant\Department;
use App\Repository\Tenant\BranchRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DepartmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Ingrese el nombre del departamento',
                    'class' => 'form-control',
                    'maxlength' => 255
                ]
            ])
            ->add('code', TextType::class, [
                'label' => 'Código',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Código identificador (opcional)',
                    'class' => 'form-control',
                    'maxlength' => 100
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Descripción del departamento',
                    'class' => 'form-control',
                    'rows' => 3
                ]
            ])
            ->add('branch', EntityType::class, [
                'label' => 'Sucursal',
                'class' => Branch::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Seleccione una sucursal',
                'query_builder' => function(BranchRepository $repository) {
                    return $repository->createQueryBuilder('b')
                        ->where('b.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('b.name', 'ASC');
                },
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
            'data_class' => Department::class,
        ]);
    }
}
