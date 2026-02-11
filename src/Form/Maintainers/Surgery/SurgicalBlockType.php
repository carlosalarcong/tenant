<?php

namespace App\Form\Maintainers\Surgery;

use App\Entity\Tenant\MedicalService;
use App\Entity\Tenant\SurgicalBlock;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurgicalBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: Pabellón 1'
                ]
            ])
            ->add('medicalService', EntityType::class, [
                'label' => 'Servicio Médico',
                'class' => MedicalService::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Seleccione un servicio médico',
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SurgicalBlock::class,
        ]);
    }
}
