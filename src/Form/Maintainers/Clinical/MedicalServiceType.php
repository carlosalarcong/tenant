<?php

namespace App\Form\Maintainers\Clinical;

use App\Entity\Tenant\Department;
use App\Entity\Tenant\MedicalService;
use App\Repository\Tenant\DepartmentRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MedicalServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => ['placeholder' => 'Nombre del servicio', 'class' => 'form-control', 'maxlength' => 255]
            ])
            ->add('code', TextType::class, [
                'label' => 'Código',
                'required' => false,
                'attr' => ['placeholder' => 'Código (opcional)', 'class' => 'form-control', 'maxlength' => 100]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => ['placeholder' => 'Descripción del servicio', 'class' => 'form-control', 'rows' => 3]
            ])
            ->add('hl7ServiceType', TextType::class, [
                'label' => 'Tipo Servicio HL7',
                'required' => false,
                'attr' => ['placeholder' => 'Código HL7 (opcional)', 'class' => 'form-control', 'maxlength' => 50]
            ])
            ->add('department', EntityType::class, [
                'label' => 'Departamento',
                'class' => Department::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Seleccione un departamento',
                'query_builder' => function(DepartmentRepository $repository) {
                    return $repository->createQueryBuilder('d')
                        ->where('d.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('d.name', 'ASC');
                },
                'attr' => ['class' => 'form-select']
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => MedicalService::class]);
    }
}
