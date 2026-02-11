<?php

namespace App\Form\Maintainers\Hospital;

use App\Entity\Tenant\PrescriptionRuleDetail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrescriptionRuleDetailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('intervals', TextType::class, [
                'label' => 'Intervalos',
                'attr' => [
                    'placeholder' => 'Ej: 08:00,14:00,20:00'
                ]
            ])
            ->add('dailyQuantity', IntegerType::class, [
                'label' => 'Cantidad por Dia',
                'attr' => [
                    'min' => 1
                ]
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PrescriptionRuleDetail::class,
        ]);
    }
}
