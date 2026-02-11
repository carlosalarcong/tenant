<?php

namespace App\Form\Maintainers\Treasury;

use App\Entity\Tenant\CreditCard;
use App\Entity\Tenant\CreditCardType;
use App\Repository\Tenant\CreditCardTypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreditCardFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'required' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('abbreviation', TextType::class, [
                'label' => 'Abreviacion',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ej: VISA, MC',
                    'class' => 'form-control'
                ]
            ])
            ->add('creditCardType', EntityType::class, [
                'label' => 'Tipo de Tarjeta',
                'class' => CreditCardType::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Seleccione tipo...',
                'query_builder' => function(CreditCardTypeRepository $repository) {
                    return $repository->createQueryBuilder('cct')
                        ->where('cct.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('cct.name', 'ASC');
                },
                'attr' => ['class' => 'form-select']
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
            'data_class' => CreditCard::class,
        ]);
    }
}
