<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\Payer;
use App\Entity\Tenant\PayerType as PayerTypeEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PayerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('payerType', EntityType::class, [
                'label' => 'Tipo de Financiador',
                'class' => PayerTypeEntity::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione un tipo',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank(['message' => 'El tipo de financiador es obligatorio'])],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('pt')
                        ->where('pt.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('pt.name', 'ASC');
                }
            ])
            ->add('code', TextType::class, [
                'label' => 'Código',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Código único', 'maxlength' => 20]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nombre del financiador', 'maxlength' => 150],
                'constraints' => [new Assert\NotBlank(['message' => 'El nombre es obligatorio'])]
            ])
            ->add('shortName', TextType::class, [
                'label' => 'Nombre Corto',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nombre abreviado', 'maxlength' => 50]
            ])
            ->add('rut', TextType::class, [
                'label' => 'RUT',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '12.345.678-9', 'maxlength' => 20]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'email@financiador.cl', 'maxlength' => 100]
            ])
            ->add('phone', TelType::class, [
                'label' => 'Teléfono',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '+56 2 1234 5678', 'maxlength' => 50]
            ])
            ->add('address', TextareaType::class, [
                'label' => 'Dirección',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'Dirección completa']
            ])
            ->add('discountPercentage', NumberType::class, [
                'label' => 'Porcentaje de Descuento (%)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '0.00', 'step' => '0.01']
            ])
            ->add('paymentDays', IntegerType::class, [
                'label' => 'Días de Pago',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Días para pago']
            ])
            ->add('requiresAuthorization', CheckboxType::class, [
                'label' => '¿Requiere Autorización?',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
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
            'data_class' => Payer::class,
        ]);
    }
}
