<?php

namespace App\Form\Maintainers\Budget;

use App\Entity\Tenant\BudgetFooter;
use App\Entity\Tenant\BudgetFunderFooter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BudgetFunderFooterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ingrese nombre'
                ]
            ])
            ->add('budgetFooter', EntityType::class, [
                'class' => BudgetFooter::class,
                'choice_label' => 'name',
                'label' => 'Pie Presupuesto',
                'placeholder' => 'Seleccione pie presupuesto...',
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('bf')
                        ->where('bf.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('bf.name', 'ASC');
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
            'data_class' => BudgetFunderFooter::class,
        ]);
    }
}
