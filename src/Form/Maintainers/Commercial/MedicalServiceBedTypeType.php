<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\BedType;
use App\Entity\Tenant\MedicalService;
use App\Entity\Tenant\MedicalServiceBedType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MedicalServiceBedTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('medicalService', EntityType::class, [
                'label' => 'Acción Clínica',
                'class' => MedicalService::class,
                'choice_label' => function (MedicalService $service) {
                    return $service->getCode() . ' - ' . $service->getName();
                },
                'placeholder' => 'Seleccione una acción clínica',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank(['message' => 'La acción clínica es obligatoria'])],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('ms')
                        ->where('ms.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('ms.code', 'ASC');
                }
            ])
            ->add('bedType', EntityType::class, [
                'label' => 'Tipo de Cama',
                'class' => BedType::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione un tipo de cama',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank(['message' => 'El tipo de cama es obligatorio'])],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('bt')
                        ->where('bt.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('bt.name', 'ASC');
                }
            ])
            ->add('quantity', IntegerType::class, [
                'label' => 'Cantidad',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Cantidad de camas requeridas'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La cantidad es obligatoria']),
                    new Assert\Positive(['message' => 'La cantidad debe ser mayor a 0'])
                ]
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
            'data_class' => MedicalServiceBedType::class,
        ]);
    }
}
