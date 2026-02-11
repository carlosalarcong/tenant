<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\ENOPathology;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ENOPathologyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Código',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Código de la patología', 'maxlength' => 20]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nombre de la patología ENO', 'maxlength' => 200],
                'constraints' => [new Assert\NotBlank(['message' => 'El nombre es obligatorio'])]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Descripción de la patología']
            ])
            ->add('icd10Code', TextType::class, [
                'label' => 'Código CIE-10',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Código de clasificación internacional', 'maxlength' => 20]
            ])
            ->add('requiresSpecialist', CheckboxType::class, [
                'label' => '¿Requiere Especialista?',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('isChronic', CheckboxType::class, [
                'label' => '¿Es Crónica?',
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
            'data_class' => ENOPathology::class,
        ]);
    }
}
