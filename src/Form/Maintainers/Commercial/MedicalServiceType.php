<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\BudgetItem;
use App\Entity\Tenant\Figure;
use App\Entity\Tenant\MedicalService;
use App\Entity\Tenant\ServiceType;
use App\Entity\Tenant\SubCompany;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class MedicalServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Código',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Código único', 'maxlength' => 20],
                'constraints' => [new Assert\NotBlank(['message' => 'El código es obligatorio'])]
            ])
            ->add('fonasaCode', TextType::class, [
                'label' => 'Código Fonasa',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Código Fonasa', 'maxlength' => 20]
            ])
            ->add('name', TextareaType::class, [
                'label' => 'Nombre',
                'attr' => ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'Nombre de la acción clínica'],
                'constraints' => [new Assert\NotBlank(['message' => 'El nombre es obligatorio'])]
            ])
            ->add('shortName', TextType::class, [
                'label' => 'Nombre Corto',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nombre abreviado', 'maxlength' => 45]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Descripción detallada']
            ])
            ->add('accountingCodeErp', IntegerType::class, [
                'label' => 'Código Contable ERP',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Código para contabilidad']
            ])
            ->add('duration', TimeType::class, [
                'label' => 'Duración',
                'required' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('isImed', CheckboxType::class, [
                'label' => '¿Es IMED?',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('imedCode', TextType::class, [
                'label' => 'Código IMED',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Código IMED', 'maxlength' => 20]
            ])
            ->add('interfaceCode', TextType::class, [
                'label' => 'Código de Interface',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Código para interfaces', 'maxlength' => 20]
            ])
            ->add('participation', IntegerType::class, [
                'label' => 'Participación',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Porcentaje de participación']
            ])
            ->add('appliesSurcharge', CheckboxType::class, [
                'label' => '¿Aplica Recargo?',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('isTaxed', CheckboxType::class, [
                'label' => '¿Está Gravado?',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('isProcedure', CheckboxType::class, [
                'label' => '¿Es Procedimiento?',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('figure', EntityType::class, [
                'label' => 'Figura',
                'class' => Figure::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione una figura',
                'required' => false,
                'attr' => ['class' => 'form-select'],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('f')
                        ->where('f.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('f.name', 'ASC');
                }
            ])
            ->add('budgetItem', EntityType::class, [
                'label' => 'Item Presupuestario',
                'class' => BudgetItem::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione un item',
                'required' => false,
                'attr' => ['class' => 'form-select'],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('bi')
                        ->where('bi.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('bi.name', 'ASC');
                }
            ])
            ->add('serviceType', EntityType::class, [
                'label' => 'Tipo de Servicio',
                'class' => ServiceType::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione un tipo',
                'required' => false,
                'attr' => ['class' => 'form-select'],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('st')
                        ->where('st.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('st.name', 'ASC');
                }
            ])
            ->add('subCompany', EntityType::class, [
                'label' => 'Sub Empresa',
                'class' => SubCompany::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione una sub empresa',
                'required' => false,
                'attr' => ['class' => 'form-select'],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('sc')
                        ->where('sc.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('sc.name', 'ASC');
                }
            ])
            ->add('billingSubCompany', EntityType::class, [
                'label' => 'Sub Empresa Facturación',
                'class' => SubCompany::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione una sub empresa',
                'required' => false,
                'attr' => ['class' => 'form-select'],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('sc')
                        ->where('sc.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('sc.name', 'ASC');
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
            'data_class' => MedicalService::class,
        ]);
    }
}
