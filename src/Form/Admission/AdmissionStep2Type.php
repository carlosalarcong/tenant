<?php

namespace App\Form\Admission;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
