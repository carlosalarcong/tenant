<?php

namespace App\Form\Maintainers\Logistics;

use App\Entity\Tenant\Article;
use App\Entity\Tenant\ArticleWarehouse;
use App\Entity\Tenant\Warehouse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleWarehouseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('article', EntityType::class, [
                'class' => Article::class,
                'label' => 'Artículo',
                'choice_label' => 'name',
                'placeholder' => 'Seleccione artículo...',
                'required' => true,
                'attr' => [
                    'class' => 'form-select'
                ],
                'query_builder' => function($repository) {
                    return $repository->createQueryBuilder('a')
                        ->where('a.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('a.name', 'ASC');
                }
            ])
            ->add('warehouse', EntityType::class, [
                'class' => Warehouse::class,
                'label' => 'Bodega',
                'choice_label' => 'name',
                'placeholder' => 'Seleccione bodega...',
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
            ->add('minStock', NumberType::class, [
                'label' => 'Stock Mínimo',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'step' => '0.01'
                ],
                'scale' => 2
            ])
            ->add('criticalStock', NumberType::class, [
                'label' => 'Stock Crítico',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'step' => '0.01'
                ],
                'scale' => 2
            ])
            ->add('optimalStock', NumberType::class, [
                'label' => 'Stock Óptimo',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'step' => '0.01'
                ],
                'scale' => 2
            ])
            ->add('isCritical', CheckboxType::class, [
                'label' => 'Es Crítico',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
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
            'data_class' => ArticleWarehouse::class,
        ]);
    }
}
