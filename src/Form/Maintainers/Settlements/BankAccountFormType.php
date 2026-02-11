<?php

namespace App\Form\Maintainers\Settlements;

use App\Entity\Tenant\Bank;
use App\Entity\Tenant\BankAccount;
use App\Entity\Tenant\BankAccountType as BankAccountTypeEntity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankAccountFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('accountNumber', TextType::class, [
                'label' => 'Numero de Cuenta',
                'attr' => [
                    'placeholder' => 'Ingrese numero de cuenta',
                    'class' => 'form-control'
                ]
            ])
            ->add('bank', EntityType::class, [
                'class' => Bank::class,
                'label' => 'Banco',
                'choice_label' => 'name',
                'placeholder' => 'Seleccione banco...',
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ],
                'query_builder' => function ($er) {
                    return $er->createQueryBuilder('b')
                        ->where('b.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('b.name', 'ASC');
                }
            ])
            ->add('bankAccountType', EntityType::class, [
                'class' => BankAccountTypeEntity::class,
                'label' => 'Tipo de Cuenta',
                'choice_label' => 'name',
                'placeholder' => 'Seleccione tipo...',
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ],
                'query_builder' => function ($er) {
                    return $er->createQueryBuilder('bat')
                        ->where('bat.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('bat.name', 'ASC');
                }
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BankAccount::class,
        ]);
    }
}
