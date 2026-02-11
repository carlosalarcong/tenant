<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\MedicalService;
use App\Entity\Tenant\MedicalServiceService;
use App\Entity\Tenant\Service;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MedicalServiceServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('medicalService', EntityType::class, [
                'label' => 'Acción Clínica',
                'class' => MedicalService::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione una acción clínica',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank(['message' => 'La acción clínica es obligatoria'])],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('ms')
                        ->where('ms.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('ms.name', 'ASC');
                }
            ])
            ->add('service', EntityType::class, [
                'label' => 'Servicio',
                'class' => Service::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione un servicio',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank(['message' => 'El servicio es obligatorio'])],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('s')
                        ->where('s.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('s.name', 'ASC');
                }
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
            'data_class' => MedicalServiceService::class,
        ]);
    }
}
