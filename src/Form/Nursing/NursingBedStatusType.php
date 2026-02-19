<?php

namespace App\Form\Nursing;

use App\Entity\Tenant\Bed;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NursingBedStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'label' => 'Estado de cama',
                'choices' => [
                    'Disponible' => 'available',
                    'Ocupada' => 'occupied',
                    'Mantenimiento' => 'maintenance',
                    'Reservada' => 'reserved',
                    'Limpieza' => 'cleaning',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('observations', TextareaType::class, [
                'label' => 'Observación',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Detalle del cambio de estado',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Bed::class,
        ]);
    }
}
