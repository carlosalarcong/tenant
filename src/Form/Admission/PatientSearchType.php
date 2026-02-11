<?php

namespace App\Form\Admission;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PatientSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('identification_type', ChoiceType::class, [
                'required' => false,
                'choices' => $options['identification_choices'],
                'label' => 'Tipo documento',
            ])
            ->add('q', TextType::class, [
                'required' => false,
                'label' => 'RUT / Documento / Nombre',
                'attr' => [
                    'placeholder' => 'Ejemplo: 12345678-9 o Carlos Alarcón',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
            'identification_choices' => [],
        ]);

        $resolver->setAllowedTypes('identification_choices', 'array');
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
