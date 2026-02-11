<?php

namespace App\Form\Maintainers\Surgery;

use App\Entity\Tenant\SurgeryItem;
use App\Entity\Tenant\SurgicalTeamRole;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurgicalTeamRoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Orden',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: 1'
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Cirujano Principal'
                ]
            ])
            ->add('surgeryItem', EntityType::class, [
                'label' => 'Item Cirugía',
                'class' => SurgeryItem::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Seleccione un item de cirugía',
                'attr' => [
                    'class' => 'form-select'
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
            'data_class' => SurgicalTeamRole::class,
        ]);
    }
}
