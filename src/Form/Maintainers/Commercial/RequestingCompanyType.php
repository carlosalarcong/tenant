<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\RequestingCompany;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RequestingCompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Código',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Código único', 'maxlength' => 20]
            ])
            ->add('businessName', TextType::class, [
                'label' => 'Razón Social',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Razón social de la empresa', 'maxlength' => 150],
                'constraints' => [new Assert\NotBlank(['message' => 'La razón social es obligatoria'])]
            ])
            ->add('tradeName', TextType::class, [
                'label' => 'Nombre Comercial',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nombre comercial (opcional)', 'maxlength' => 50]
            ])
            ->add('rut', TextType::class, [
                'label' => 'RUT',
                'attr' => ['class' => 'form-control', 'placeholder' => '12.345.678-9', 'maxlength' => 20],
                'constraints' => [new Assert\NotBlank(['message' => 'El RUT es obligatorio'])]
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Dirección',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'Dirección completa']
            ])
            ->add('contactPerson', TextType::class, [
                'label' => 'Persona de Contacto',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nombre del contacto', 'maxlength' => 100]
            ])
            ->add('phone', TelType::class, [
                'label' => 'Teléfono',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '+56 9 1234 5678', 'maxlength' => 50]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'email@empresa.cl', 'maxlength' => 100]
            ])
            ->add('industry', TextType::class, [
                'label' => 'Industria/Rubro',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ej: Minería, Retail, etc.', 'maxlength' => 100]
            ])
            ->add('numberOfEmployees', IntegerType::class, [
                'label' => 'Número de Empleados',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Cantidad de empleados']
            ])
            ->add('discountPercentage', NumberType::class, [
                'label' => 'Porcentaje de Descuento (%)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '0.00', 'step' => '0.01']
            ])
            ->add('paymentTermDays', IntegerType::class, [
                'label' => 'Días de Plazo de Pago',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Días de crédito']
            ])
            ->add('hasAgreement', CheckboxType::class, [
                'label' => '¿Tiene Convenio?',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('agreementStartDate', DateType::class, [
                'label' => 'Fecha Inicio Convenio',
                'required' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('agreementEndDate', DateType::class, [
                'label' => 'Fecha Fin Convenio',
                'required' => false,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
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
            'data_class' => RequestingCompany::class,
        ]);
    }
}
