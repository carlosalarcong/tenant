<?php

namespace App\Form\Revenue\Payment\Method;

use App\Entity\Tenant\Bank;
use App\Repository\Tenant\BankRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankTransferPaymentType extends AbstractType
{
    public function __construct(
        private readonly BankRepository $bankRepository
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
            ->add('transfer_number', TextType::class, [
                'required' => true,
                'attr' => [
                    'maxlength' => 30,
                    'placeholder' => 'N° operación',
                ],
            ])
            ->add('account_holder', TextType::class, [
                'required' => false,
                'attr' => [
                    'maxlength' => 100,
                    'placeholder' => 'Titular de la cuenta',
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
                    'placeholder' => 'Ej: 150000',
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
