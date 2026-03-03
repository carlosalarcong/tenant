<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\BillingItem;
use App\Entity\Tenant\SurgeryPackage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SurgeryPackageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre del Paquete',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [new Assert\NotBlank(['message' => 'El nombre del paquete es obligatorio'])],
            ])
            ->add('billingItem', EntityType::class, [
                'label' => 'Prestación Principal',
                'class' => BillingItem::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione prestación',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank(['message' => 'La prestación principal es obligatoria'])],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('bi')
                        ->orderBy('bi.name', 'ASC');
                },
            ])
            ->add('adjustmentPercentage', NumberType::class, [
                'label' => '% Ajuste',
                'required' => true,
                'scale' => 2,
                'attr' => ['class' => 'form-control'],
                'constraints' => [new Assert\NotBlank(['message' => 'El porcentaje de ajuste es obligatorio'])],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SurgeryPackage::class,
        ]);
    }
}
