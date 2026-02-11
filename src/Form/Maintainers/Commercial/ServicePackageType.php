<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\ServicePackage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ServicePackageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Código',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Código del paquete', 'maxlength' => 50],
                'constraints' => [new Assert\NotBlank(['message' => 'El código es obligatorio'])]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nombre del paquete', 'maxlength' => 255],
                'constraints' => [new Assert\NotBlank(['message' => 'El nombre es obligatorio'])]
            ])
            ->add('isBillable', CheckboxType::class, [
                'label' => '¿Es Facturable?',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('isProgram', CheckboxType::class, [
                'label' => '¿Es Programa?',
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
            'data_class' => ServicePackage::class,
        ]);
    }
}
