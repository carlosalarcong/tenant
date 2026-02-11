<?php

namespace App\Form\Admission;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class AdmissionStep2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('branch', IntegerType::class, [
                'required' => false,
                'empty_data' => '1',
            ])
            ->add('professional', IntegerType::class, [
                'required' => false,
            ])
            ->add('specialty', IntegerType::class, [
                'required' => false,
            ])
            ->add('origin', IntegerType::class, [
                'required' => false,
            ])
            ->add('payer', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debes seleccionar financiador.'),
                    new Positive(message: 'Debes seleccionar financiador.'),
                ],
            ])
            ->add('agreement', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debes seleccionar convenio.'),
                    new Positive(message: 'Debes seleccionar convenio.'),
                ],
            ])
            ->add('service', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debes seleccionar servicio.'),
                    new Positive(message: 'Debes seleccionar servicio.'),
                ],
            ])
            ->add('bed', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'Debes seleccionar cama.'),
                    new Positive(message: 'Debes seleccionar cama.'),
                ],
            ])
            ->add('referralDoctor', TextType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('emergencyContact', TextType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('emergencyPhone', TextType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('childrenCount', IntegerType::class, [
                'required' => false,
            ])
            ->add('tutorDocument', TextType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('tutorName', TextType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('observations', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('medicalOrder', CheckboxType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
