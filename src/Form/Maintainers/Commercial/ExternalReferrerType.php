<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\ExternalReferrer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ExternalReferrerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Código',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Código único', 'maxlength' => 20]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nombre del derivador', 'maxlength' => 150],
                'constraints' => [new Assert\NotBlank(['message' => 'El nombre es obligatorio'])]
            ])
            ->add('shortName', TextType::class, [
                'label' => 'Nombre Corto',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nombre abreviado', 'maxlength' => 50]
            ])
            ->add('referrerType', TextType::class, [
                'label' => 'Tipo de Derivador',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ej: Hospital, Clínica, Centro de Salud', 'maxlength' => 100]
            ])
            ->add('rut', TextType::class, [
                'label' => 'RUT',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '12.345.678-9', 'maxlength' => 20]
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Dirección',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'Dirección completa']
            ])
            ->add('contactPerson', TextType::class, [
                'label' => 'Persona de Contacto',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nombre del contacto', 'maxlength' => 100]
            ])
            ->add('phone', TelType::class, [
                'label' => 'Teléfono',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '+56 9 1234 5678', 'maxlength' => 50]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'email@derivador.cl', 'maxlength' => 100]
            ])
            ->add('hasAgreement', CheckboxType::class, [
                'label' => '¿Tiene Convenio?',
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
            'data_class' => ExternalReferrer::class,
        ]);
    }
}
