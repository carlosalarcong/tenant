<?php

namespace App\Form\Revenue\Payment\Method;

use App\Entity\Tenant\CreditCard;
use App\Repository\Tenant\CreditCardRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditCardPaymentType extends AbstractType
{
    public function __construct(
        private readonly CreditCardRepository $creditCardRepository
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('card_id', EntityType::class, [
                'class' => CreditCard::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'query_builder' => $this->creditCardRepository->createQueryBuilder('cc')
                    ->where('cc.isActive = :active')
                    ->setParameter('active', true)
                    ->orderBy('cc.name', 'ASC'),
                'placeholder' => 'Seleccionar tarjeta',
                'required' => true,
            ])
            ->add('voucher', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => 12,
                ],
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
