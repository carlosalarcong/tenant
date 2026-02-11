<?php

namespace App\Form\Maintainers\Logistics;

use App\Entity\Tenant\Article;
use App\Entity\Tenant\ArticleType;
use App\Entity\Tenant\BudgetItem;
use App\Entity\Tenant\SubCompany;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Grupo: Identificación
            ->add('code', TextType::class, [
                'label' => 'Código',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Código del artículo'
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nombre del artículo'
                ]
            ])
            ->add('shortName', TextType::class, [
                'label' => 'Nombre Abreviado',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nombre corto'
                ]
            ])
            ->add('genericName', TextType::class, [
                'label' => 'Nombre Genérico',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nombre genérico'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3
                ]
            ])
            
            // Grupo: Clasificación
            ->add('articleType', EntityType::class, [
                'class' => ArticleType::class,
                'label' => 'Tipo de Artículo',
                'choice_label' => 'name',
                'placeholder' => 'Seleccione tipo...',
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ],
                'query_builder' => function($repository) {
                    return $repository->createQueryBuilder('at')
                        ->where('at.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('at.name', 'ASC');
                }
            ])
            ->add('subCompany', EntityType::class, [
                'class' => SubCompany::class,
                'label' => 'Sub Empresa',
                'choice_label' => 'name',
                'placeholder' => 'Seleccione...',
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ],
                'query_builder' => function($repository) {
                    return $repository->createQueryBuilder('sc')
                        ->where('sc.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('sc.name', 'ASC');
                }
            ])
            ->add('billingSubCompany', EntityType::class, [
                'class' => SubCompany::class,
                'label' => 'Sub Empresa Facturadora',
                'choice_label' => 'name',
                'placeholder' => 'Seleccione...',
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ],
                'query_builder' => function($repository) {
                    return $repository->createQueryBuilder('sc')
                        ->where('sc.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('sc.name', 'ASC');
                }
            ])
            ->add('budgetItem', EntityType::class, [
                'class' => BudgetItem::class,
                'label' => 'Item Presupuestario',
                'choice_label' => 'name',
                'placeholder' => 'Seleccione...',
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ],
                'query_builder' => function($repository) {
                    return $repository->createQueryBuilder('bi')
                        ->where('bi.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('bi.name', 'ASC');
                }
            ])
            
            // Grupo: Códigos
            ->add('accountGroupCode', TextType::class, [
                'label' => 'Cód. Agrupación Cuenta',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('iconCode', TextType::class, [
                'label' => 'Código Ícono',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('cenabastCode', TextType::class, [
                'label' => 'Código Cenabast',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            
            // Grupo: Stock
            ->add('minStock', NumberType::class, [
                'label' => 'Stock Mínimo',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'scale' => 2
            ])
            ->add('criticalStock', NumberType::class, [
                'label' => 'Stock Crítico',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'scale' => 2
            ])
            ->add('optimalStock', NumberType::class, [
                'label' => 'Stock Óptimo',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'scale' => 2
            ])
            ->add('maxStock', NumberType::class, [
                'label' => 'Stock Máximo',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'scale' => 2
            ])
            ->add('margin', NumberType::class, [
                'label' => 'Margen',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'scale' => 2
            ])
            
            // Grupo: Propiedades (checkboxes)
            ->add('isConsignment', CheckboxType::class, [
                'label' => 'Es Consignación',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('isControlled', CheckboxType::class, [
                'label' => 'Es Controlado',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('hasExpirationDate', CheckboxType::class, [
                'label' => 'Tiene Fecha Vencimiento',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('isCritical', CheckboxType::class, [
                'label' => 'Es Crítico',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('isGeneric', CheckboxType::class, [
                'label' => 'Es Genérico',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('isResterilizable', CheckboxType::class, [
                'label' => 'Es Reesterilizable',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('isForSale', CheckboxType::class, [
                'label' => 'Es Venta',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('isBillable', CheckboxType::class, [
                'label' => 'Es Facturable',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
            ->add('isFirstAidDeduction', CheckboxType::class, [
                'label' => 'Rebaja Botiquín',
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
            ])
            
            // Grupo: Imagen
            ->add('photoName', TextType::class, [
                'label' => 'Nombre Foto',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
