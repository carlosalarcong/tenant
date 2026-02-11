<?php

namespace App\Form\Maintainers\Clinical;

use App\Entity\Tenant\ServiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => ['placeholder' => 'Nombre del tipo de servicio', 'class' => 'form-control', 'maxlength' => 255]
            ])
            ->add('code', TextType::class, [
                'label' => 'Código',
                'required' => false,
                'attr' => ['placeholder' => 'Código (opcional)', 'class' => 'form-control', 'maxlength' => 100]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => ['placeholder' => 'Descripción del tipo de servicio', 'class' => 'form-control', 'rows' => 3]
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ServiceType::class]);
    }
}
