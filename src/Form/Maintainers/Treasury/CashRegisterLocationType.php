<?php

namespace App\Form\Maintainers\Treasury;

use App\Entity\Tenant\Branch;
use App\Entity\Tenant\CashRegisterLocation;
use App\Repository\Tenant\BranchRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CashRegisterLocationType extends AbstractType
{
    public function __construct(
        private readonly BranchRepository $branchRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => [
                    'maxlength' => 50,
                    'placeholder' => 'Ingrese el nombre de la ubicación'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Ingrese una descripción (opcional)'
                ]
            ])
            ->add('branch', EntityType::class, [
                'class' => Branch::class,
                'choice_label' => 'name',
                'label' => 'Sucursal',
                'placeholder' => 'Seleccione una sucursal',
                'required' => false,
                'query_builder' => function () {
                    return $this->branchRepository->createQueryBuilder('b')
                        ->where('b.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('b.name', 'ASC');
                }
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CashRegisterLocation::class,
        ]);
    }
}
