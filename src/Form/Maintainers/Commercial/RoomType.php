<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\Clinic;
use App\Entity\Tenant\Room;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('roomNumber', TextType::class, [
                'label' => 'Número de Sala',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ej: 101, A-23', 'maxlength' => 50],
                'constraints' => [new Assert\NotBlank(['message' => 'El número de sala es obligatorio'])]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nombre descriptivo', 'maxlength' => 150],
                'constraints' => [new Assert\NotBlank(['message' => 'El nombre es obligatorio'])]
            ])
            ->add('clinic', EntityType::class, [
                'label' => 'Clínica',
                'class' => Clinic::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione una clínica',
                'required' => false,
                'attr' => ['class' => 'form-select'],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('c')
                        ->where('c.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('c.name', 'ASC');
                }
            ])
            ->add('roomType', ChoiceType::class, [
                'label' => 'Tipo de Sala',
                'choices' => [
                    'Habitación de Paciente' => 'patient',
                    'Sala de Operaciones' => 'operating',
                    'Emergencias' => 'emergency',
                    'UCI' => 'icu',
                    'Consulta' => 'consultation'
                ],
                'placeholder' => 'Seleccione un tipo',
                'required' => false,
                'attr' => ['class' => 'form-select']
            ])
            ->add('floor', TextType::class, [
                'label' => 'Piso',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ej: 1, 2, PB', 'maxlength' => 50]
            ])
            ->add('wing', TextType::class, [
                'label' => 'Ala/Sector',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ej: Norte, Sur, A, B', 'maxlength' => 100]
            ])
            ->add('capacity', IntegerType::class, [
                'label' => 'Capacidad',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Número de camas'],
                'constraints' => [new Assert\Positive()]
            ])
            ->add('dailyRate', NumberType::class, [
                'label' => 'Tarifa Diaria',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '0.00', 'step' => '0.01']
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Estado',
                'choices' => [
                    'Disponible' => 'available',
                    'Ocupada' => 'occupied',
                    'Mantenimiento' => 'maintenance',
                    'Reservada' => 'reserved',
                    'Limpieza' => 'cleaning'
                ],
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank()]
            ])
            ->add('hasOxygen', CheckboxType::class, [
                'label' => '¿Tiene Oxígeno?',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('hasBathroom', CheckboxType::class, [
                'label' => '¿Tiene Baño?',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('observations', TextareaType::class, [
                'label' => 'Observaciones',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Observaciones adicionales']
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
            'data_class' => Room::class,
        ]);
    }
}
