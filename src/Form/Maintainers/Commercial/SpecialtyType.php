<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\Specialty;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SpecialtyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Código',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Código de la especialidad', 'maxlength' => 20]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nombre de la especialidad', 'maxlength' => 100],
                'constraints' => [new Assert\NotBlank(['message' => 'El nombre es obligatorio'])]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Descripción de la especialidad']
            ])
            ->add('category', TextType::class, [
                'label' => 'Categoría',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ej: Médica, Quirúrgica, etc.', 'maxlength' => 100]
            ])
            ->add('requiresCertification', CheckboxType::class, [
                'label' => '¿Requiere Certificación?',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('defaultConsultationDuration', IntegerType::class, [
                'label' => 'Duración Default de Consulta (minutos)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ej: 30']
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
            'data_class' => Specialty::class,
        ]);
    }
}
