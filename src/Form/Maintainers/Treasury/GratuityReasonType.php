<?php

namespace App\Form\Maintainers\Treasury;

use App\Entity\Tenant\Branch;
use App\Entity\Tenant\GratuityReason;
use App\Entity\Tenant\GratuityType;
use App\Repository\Tenant\BranchRepository;
use App\Repository\Tenant\GratuityTypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GratuityReasonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('gratuityType', EntityType::class, [
                'label' => 'Tipo de Gratuidad',
                'class' => GratuityType::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Seleccione...',
                'query_builder' => function(GratuityTypeRepository $repository) {
                    return $repository->createQueryBuilder('gt')
                        ->where('gt.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('gt.name', 'ASC');
                },
                'attr' => ['class' => 'form-select']
            ])
            ->add('branch', EntityType::class, [
                'label' => 'Sucursal',
                'class' => Branch::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Seleccione...',
                'query_builder' => function(BranchRepository $repository) {
                    return $repository->createQueryBuilder('b')
                        ->where('b.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('b.name', 'ASC');
                },
                'attr' => ['class' => 'form-select']
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
            'data_class' => GratuityReason::class,
        ]);
    }
}
