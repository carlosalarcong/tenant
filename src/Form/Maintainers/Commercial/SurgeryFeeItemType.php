<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\Branch;
use App\Entity\Tenant\SurgeryFeeItem;
use App\Entity\Tenant\SurgeryFeeItemType as SurgeryFeeItemTypeEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SurgeryFeeItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [new Assert\NotBlank(['message' => 'El nombre es obligatorio'])],
            ])
            ->add('budgetName', TextType::class, [
                'label' => 'Nombre Presupuesto',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [new Assert\NotBlank(['message' => 'El nombre de presupuesto es obligatorio'])],
            ])
            ->add('displayOrder', IntegerType::class, [
                'label' => 'Orden',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [new Assert\NotBlank(['message' => 'El orden es obligatorio'])],
            ])
            ->add('taxRate', IntegerType::class, [
                'label' => 'Impuesto (%)',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('isMedicalTeam', CheckboxType::class, [
                'label' => 'Es Equipo Médico',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('isEditable', CheckboxType::class, [
                'label' => 'Es Editable',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('itemType', EntityType::class, [
                'label' => 'Tipo Ítem',
                'class' => SurgeryFeeItemTypeEntity::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione tipo',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank(['message' => 'El tipo de ítem es obligatorio'])],
            ])
            ->add('branch', EntityType::class, [
                'label' => 'Sucursal',
                'class' => Branch::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione sucursal',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank(['message' => 'La sucursal es obligatoria'])],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('b')
                        ->where('b.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('b.name', 'ASC');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SurgeryFeeItem::class,
        ]);
    }
}
