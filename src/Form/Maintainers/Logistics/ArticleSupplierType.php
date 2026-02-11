<?php

namespace App\Form\Maintainers\Logistics;

use App\Entity\Tenant\Article;
use App\Entity\Tenant\ArticleSupplier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleSupplierType extends AbstractType
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
            ->add('supplierName', TextType::class, [
                'label' => 'Proveedor',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nombre del proveedor'
                ]
            ])
            ->add('price', NumberType::class, [
                'label' => 'Precio',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'step' => '0.01'
                ],
                'scale' => 2
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
            'data_class' => ArticleSupplier::class,
        ]);
    }
}
