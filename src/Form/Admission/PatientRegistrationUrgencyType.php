<?php

namespace App\Form\Admission;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PatientRegistrationUrgencyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('triage', ChoiceType::class, [
                'required' => true,
                'placeholder' => '-- Seleccionar --',
                'choices' => [
                    'C1 - Reanimación' => 'c1',
                    'C2 - Emergencia' => 'c2',
                    'C3 - Urgencia' => 'c3',
                    'C4 - Menor urgencia' => 'c4',
                    'C5 - No urgente' => 'c5',
                ],
                'constraints' => [
                    new NotBlank(message: 'Debe seleccionar un triage.'),
                ],
            ])
            ->add('reason', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank(message: 'El motivo de consulta es obligatorio.'),
                    new Length(max: 255),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
