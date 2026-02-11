<?php

namespace App\Form\Maintainers\Treasury;

use App\Entity\Tenant\DifferenceDirection;
use App\Entity\Tenant\DifferenceReason;
use App\Repository\Tenant\DifferenceDirectionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DifferenceReasonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('differenceDirection', EntityType::class, [
                'label' => 'Sentido Diferencia',
                'class' => DifferenceDirection::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Seleccione...',
                'query_builder' => function(DifferenceDirectionRepository $repository) {
                    return $repository->createQueryBuilder('dd')
                        ->where('dd.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('dd.name', 'ASC');
                },
                'attr' => ['class' => 'form-select']
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
            'data_class' => DifferenceReason::class,
        ]);
    }
}
