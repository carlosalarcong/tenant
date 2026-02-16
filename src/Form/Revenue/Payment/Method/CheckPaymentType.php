<?php

namespace App\Form\Revenue\Payment\Method;

use App\Entity\Tenant\Bank;
use App\Entity\Tenant\PaymentCondition;
use App\Repository\Tenant\BankRepository;
use App\Repository\Tenant\PaymentConditionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckPaymentType extends AbstractType
{
    public function __construct(
        private readonly BankRepository $bankRepository,
        private readonly PaymentConditionRepository $paymentConditionRepository
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('check_number', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => 20,
                ],
            ])
            ->add('bank_id', EntityType::class, [
                'class' => Bank::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'query_builder' => $this->bankRepository->createQueryBuilder('b')
                    ->where('b.isActive = :active')
                    ->setParameter('active', true)
                    ->orderBy('b.name', 'ASC'),
                'placeholder' => 'Seleccionar banco',
                'required' => true,
            ])
            ->add('amount', NumberType::class, [
                'required' => true,
                'scale' => 2,
                'html5' => true,
                'attr' => [
                    'step' => '0.01',
                    'min' => '0',
                    'data-payment-amount-input' => '1',
                ],
            ])
            ->add('rut', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => 12,
                    'placeholder' => '12.345.678-9',
                ],
            ])
            ->add('name', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => 255,
                ],
            ])
            ->add('condition_id', EntityType::class, [
                'class' => PaymentCondition::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'query_builder' => $this->paymentConditionRepository->createQueryBuilder('pc')
                    ->where('pc.isActive = :active')
                    ->setParameter('active', true)
                    ->orderBy('pc.name', 'ASC'),
                'placeholder' => 'Seleccionar condición',
                'required' => true,
            ])
            ->add('check_date', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'input' => 'string',
                'format' => 'yyyy-MM-dd',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
