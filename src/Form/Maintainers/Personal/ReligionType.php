<?php

namespace App\Form\Maintainers\Personal;

use App\Entity\Tenant\Religion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReligionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => [
                    'placeholder' => 'Enter religion name',
                    'class' => 'form-control'
                ]
            ])
            ->add('religionCodeHl7', TextType::class, [
                'label' => 'HL7 Code',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Enter HL7 code (optional)',
                    'class' => 'form-control',
                    'maxlength' => 10
                ]
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Active',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Religion::class,
        ]);
    }
}
