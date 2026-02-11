<?php

namespace App\Form\Maintainers\Treasury;

use App\Entity\Tenant\PaymentMethod;
use App\Entity\Tenant\PaymentMethodType as PaymentMethodTypeEntity;
use App\Repository\Tenant\PaymentMethodRepository;
use App\Repository\Tenant\PaymentMethodTypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentMethodType extends AbstractType
{
    public function __construct(
        private readonly PaymentMethodRepository $paymentMethodRepository,
        private readonly PaymentMethodTypeRepository $paymentMethodTypeRepository
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'Código',
                'required' => false,
                'attr' => [
                    'maxlength' => 10,
                    'placeholder' => 'Código del método de pago (opcional)'
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => [
                    'maxlength' => 255,
                    'placeholder' => 'Ingrese el nombre del método de pago'
                ]
            ])
            ->add('parent', EntityType::class, [
                'class' => PaymentMethod::class,
                'choice_label' => 'name',
                'label' => 'Método de Pago Padre',
                'placeholder' => 'Seleccione un método padre (opcional)',
                'required' => false,
                'query_builder' => function () {
                    return $this->paymentMethodRepository->createQueryBuilder('pm')
                        ->where('pm.isActive = :active')
                        ->andWhere('pm.parent IS NULL')
                        ->setParameter('active', true)
                        ->orderBy('pm.name', 'ASC');
                }
            ])
            ->add('paymentMethodType', EntityType::class, [
                'class' => PaymentMethodTypeEntity::class,
                'choice_label' => 'name',
                'label' => 'Tipo de Método de Pago',
                'placeholder' => 'Seleccione un tipo',
                'required' => false,
                'query_builder' => function () {
                    return $this->paymentMethodTypeRepository->createQueryBuilder('pmt')
                        ->where('pmt.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('pmt.name', 'ASC');
                }
            ])
            ->add('issuesReceipt', CheckboxType::class, [
                'label' => '¿Emite Recibo?',
                'required' => false,
            ])
            ->add('isGuarantee', CheckboxType::class, [
                'label' => '¿Es Garantía?',
                'required' => false,
            ])
            ->add('isProfessionalPayment', CheckboxType::class, [
                'label' => '¿Es Pago Profesional?',
                'required' => false,
            ])
            ->add('isWebPayment', CheckboxType::class, [
                'label' => '¿Es Pago Web?',
                'required' => false,
            ])
            ->add('documentTypeCode', TextType::class, [
                'label' => 'Código Tipo Documento',
                'required' => false,
                'attr' => [
                    'maxlength' => 10,
                    'placeholder' => 'Código del tipo de documento (opcional)'
                ]
            ])
            ->add('accountingCode', TextType::class, [
                'label' => 'Código Contable',
                'required' => false,
                'attr' => [
                    'maxlength' => 50,
                    'placeholder' => 'Código contable (opcional)'
                ]
            ])
            ->add('visibleInCashRegister', CheckboxType::class, [
                'label' => '¿Visible en Caja?',
                'required' => false,
            ])
            ->add('creditCardPayment', CheckboxType::class, [
                'label' => '¿Es Pago con Tarjeta?',
                'required' => false,
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaymentMethod::class,
        ]);
    }
}
