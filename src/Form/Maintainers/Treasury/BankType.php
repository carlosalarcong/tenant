<?php

namespace App\Form\Maintainers\Treasury;

use App\Entity\Tenant\Bank;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rut', TextType::class, [
                'label' => 'RUT',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ej: 12345678-9',
                    'class' => 'form-control'
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Nombre del banco',
                    'class' => 'form-control'
                ]
            ])
            ->add('currentAccount', IntegerType::class, [
                'label' => 'Cuenta Corriente',
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ]
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
            'data_class' => Bank::class,
        ]);
    }
}
