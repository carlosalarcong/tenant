<?php

namespace App\Form\Maintainers\Surgery;

use App\Entity\Tenant\SurgeryPatientStatus;
use App\Entity\Tenant\SurgeryPatientStatusConfig;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurgeryPatientStatusConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('surgeryPatientStatus', EntityType::class, [
                'class' => SurgeryPatientStatus::class,
                'choice_label' => 'name',
                'label' => 'Estado Paciente',
                'placeholder' => 'Seleccione un estado',
                'required' => false,
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('sps')
                        ->where('sps.isActive = :isActive')
                        ->setParameter('isActive', true)
                        ->orderBy('sps.name', 'ASC');
                }
            ])
            ->add('color', TextType::class, [
                'label' => 'Color',
                'required' => false,
                'attr' => [
                    'maxlength' => 20,
                    'placeholder' => 'Ingrese el color'
                ]
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SurgeryPatientStatusConfig::class,
        ]);
    }
}
