<?php

namespace App\Form\Maintainers\Logistics;

use App\Entity\Tenant\ArticleType;
use App\Entity\Tenant\Warehouse;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Código',
                'attr' => [
                    'placeholder' => 'Ingrese el código',
                    'class' => 'form-control',
                    'maxlength' => 255
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => [
                    'placeholder' => 'Ingrese el nombre del tipo de artículo',
                    'class' => 'form-control',
                    'maxlength' => 255
                ]
            ])
            ->add('isPharmaceutical', CheckboxType::class, [
                'label' => 'Es Fármaco',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('warehouse', EntityType::class, [
                'class' => Warehouse::class,
                'label' => 'Bodega',
                'choice_label' => 'name',
                'placeholder' => 'Seleccione una bodega...',
                'required' => false,
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
            'data_class' => ArticleType::class,
        ]);
    }
}
