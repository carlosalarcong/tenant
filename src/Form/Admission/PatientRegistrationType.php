<?php

namespace App\Form\Admission;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PatientRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $foreignMode = (bool) $options['foreign_mode'];

        $builder
            ->add('admission_type', HiddenType::class, [
                'required' => false,
                'empty_data' => 'hospitalaria',
            ])
            ->add('identification_type', ChoiceType::class, [
                'required' => !$foreignMode,
                'choices' => $options['identification_choices'],
                'placeholder' => 'Seleccionar',
                'constraints' => $foreignMode
                    ? []
                    : [new NotBlank(message: 'El tipo de documento es obligatorio.')],
            ])
            ->add('identification', TextType::class, [
                'required' => !$foreignMode,
                'constraints' => $foreignMode
                    ? [new Length(max: 50)]
                    : [
                        new NotBlank(message: 'La identificación es obligatoria.'),
                        new Length(max: 50),
                    ],
            ])
            ->add('birth_date', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'La fecha de nacimiento es obligatoria.'),
                    new Date(message: 'La fecha de nacimiento no es válida.'),
                ],
            ])
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'El nombre es obligatorio.'),
                    new Length(max: 100),
                ],
            ])
            ->add('last_name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'El apellido paterno es obligatorio.'),
                    new Length(max: 100),
                ],
            ])
            ->add('middle_name', TextType::class, [
                'required' => false,
                'constraints' => [new Length(max: 100)],
            ])
            ->add('gender', ChoiceType::class, [
                'required' => false,
                'choices' => $options['gender_choices'],
                'placeholder' => 'Seleccionar',
            ])
            ->add('country', ChoiceType::class, [
                'required' => false,
                'choices' => $options['country_choices'],
                'placeholder' => 'Seleccionar',
            ])
            ->add('marital_status', ChoiceType::class, [
                'required' => false,
                'choices' => $options['marital_status_choices'],
                'placeholder' => 'Seleccionar',
            ])
            ->add('mobile_phone', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'El teléfono móvil es obligatorio.'),
                    new Length(max: 50),
                ],
            ])
            ->add('home_phone', TextType::class, [
                'required' => false,
                'constraints' => [new Length(max: 50)],
            ])
            ->add('work_phone', TextType::class, [
                'required' => false,
                'constraints' => [new Length(max: 50)],
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'constraints' => [
                    new Email(message: 'El email principal no es válido.'),
                    new Length(max: 100),
                ],
            ])
            ->add('secondary_email', EmailType::class, [
                'required' => false,
                'constraints' => [
                    new Email(message: 'El email secundario no es válido.'),
                    new Length(max: 100),
                ],
            ])
            ->add('social_name', TextType::class, [
                'required' => false,
                'constraints' => [new Length(max: 100)],
            ])
            ->add('number_of_children', IntegerType::class, [
                'required' => false,
                'constraints' => [new GreaterThanOrEqual(0)],
            ])
            ->add('occupation', ChoiceType::class, [
                'required' => false,
                'choices' => $options['occupation_choices'],
                'placeholder' => 'Seleccionar',
            ])
            ->add('religion', ChoiceType::class, [
                'required' => false,
                'choices' => $options['religion_choices'],
                'placeholder' => 'Seleccionar',
            ])
            ->add('education_level', ChoiceType::class, [
                'required' => false,
                'choices' => $options['education_level_choices'],
                'placeholder' => 'Seleccionar',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'foreign_mode' => false,
            'identification_choices' => [],
            'gender_choices' => [],
            'country_choices' => [],
            'marital_status_choices' => [],
            'occupation_choices' => [],
            'religion_choices' => [],
            'education_level_choices' => [],
        ]);

        $resolver->setAllowedTypes('identification_choices', 'array');
        $resolver->setAllowedTypes('foreign_mode', 'bool');
        $resolver->setAllowedTypes('gender_choices', 'array');
        $resolver->setAllowedTypes('country_choices', 'array');
        $resolver->setAllowedTypes('marital_status_choices', 'array');
        $resolver->setAllowedTypes('occupation_choices', 'array');
        $resolver->setAllowedTypes('religion_choices', 'array');
        $resolver->setAllowedTypes('education_level_choices', 'array');
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
