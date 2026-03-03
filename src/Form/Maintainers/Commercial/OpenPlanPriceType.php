<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\BillingItem;
use App\Entity\Tenant\InsurancePlan;
use App\Entity\Tenant\OpenPlanPrice;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class OpenPlanPriceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plan', EntityType::class, [
                'label' => 'Plan',
                'class' => InsurancePlan::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione plan',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank(['message' => 'El plan es obligatorio'])],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('ip')
                        ->orderBy('ip.name', 'ASC');
                },
            ])
            ->add('billingItem', EntityType::class, [
                'label' => 'Prestación',
                'class' => BillingItem::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione prestación',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank(['message' => 'La prestación es obligatoria'])],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('bi')
                        ->orderBy('bi.name', 'ASC');
                },
            ])
            ->add('unitPrice', MoneyType::class, [
                'label' => 'Precio Unitario',
                'currency' => 'CLP',
                'divisor' => 1,
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [new Assert\NotBlank(['message' => 'El precio unitario es obligatorio'])],
            ])
            ->add('copayAmount', MoneyType::class, [
                'label' => 'Copago',
                'currency' => 'CLP',
                'divisor' => 1,
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [new Assert\NotBlank(['message' => 'El copago es obligatorio'])],
            ])
            ->add('theatreAmount', MoneyType::class, [
                'label' => 'Valor Pabellón',
                'currency' => 'CLP',
                'divisor' => 1,
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [new Assert\NotBlank(['message' => 'El valor de pabellón es obligatorio'])],
            ])
            ->add('effectiveDate', DateType::class, [
                'label' => 'F. Vigencia',
                'widget' => 'single_text',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [new Assert\NotBlank(['message' => 'La fecha de vigencia es obligatoria'])],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OpenPlanPrice::class,
        ]);
    }
}
