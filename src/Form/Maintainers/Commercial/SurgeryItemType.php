<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\SurgeryItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SurgeryItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Código',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Código del artículo', 'maxlength' => 20]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nombre del artículo', 'maxlength' => 200],
                'constraints' => [new Assert\NotBlank(['message' => 'El nombre es obligatorio'])]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Descripción detallada']
            ])
            ->add('category', TextType::class, [
                'label' => 'Categoría',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ej: Instrumental, Material, etc.', 'maxlength' => 100]
            ])
            ->add('unitOfMeasure', TextType::class, [
                'label' => 'Unidad de Medida',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ej: Unidad, Caja, Set', 'maxlength' => 50]
            ])
            ->add('unitCost', NumberType::class, [
                'label' => 'Costo Unitario',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '0.00', 'step' => '0.01']
            ])
            ->add('isSterile', CheckboxType::class, [
                'label' => '¿Es Estéril?',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('isDisposable', CheckboxType::class, [
                'label' => '¿Es Desechable?',
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
            'data_class' => SurgeryItem::class,
        ]);
    }
}
