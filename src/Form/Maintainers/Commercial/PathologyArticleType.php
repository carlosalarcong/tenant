<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\GESPathology;
use App\Entity\Tenant\PathologyArticle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PathologyArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('gesPathology', EntityType::class, [
                'label' => 'Patología GES',
                'class' => GESPathology::class,
                'choice_label' => function (GESPathology $pathology) {
                    return $pathology->getPathologyNumber() . ' - ' . $pathology->getName();
                },
                'placeholder' => 'Seleccione una patología GES',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank(['message' => 'La patología GES es obligatoria'])],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('gp')
                        ->where('gp.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('gp.pathologyNumber', 'ASC');
                }
            ])
            ->add('articleName', TextType::class, [
                'label' => 'Nombre del Artículo',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nombre del artículo o insumo', 'maxlength' => 200],
                'constraints' => [new Assert\NotBlank(['message' => 'El nombre del artículo es obligatorio'])]
            ])
            ->add('articleCode', TextType::class, [
                'label' => 'Código del Artículo',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Código único', 'maxlength' => 20]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Descripción detallada']
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Cantidad',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Cantidad requerida'],
                'constraints' => [new Assert\Positive(['message' => 'La cantidad debe ser mayor a 0'])]
            ])
            ->add('unitOfMeasure', TextType::class, [
                'label' => 'Unidad de Medida',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ej: Unidad, Caja, ml', 'maxlength' => 50]
            ])
            ->add('unitCost', NumberType::class, [
                'label' => 'Costo Unitario',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '0.00', 'step' => '0.01']
            ])
            ->add('isMandatory', CheckboxType::class, [
                'label' => '¿Es Obligatorio?',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
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
            'data_class' => PathologyArticle::class,
        ]);
    }
}
