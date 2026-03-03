<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\BillingItem;
use App\Entity\Tenant\BranchCareType;
use App\Entity\Tenant\BranchPayer;
use App\Entity\Tenant\InsurancePlan;
use App\Entity\Tenant\InsurancePlanPrice;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class InsurancePlanPriceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('insurancePlan', EntityType::class, [
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
            ->add('branchPayer', EntityType::class, [
                'label' => 'Sucursal/Financiador',
                'class' => BranchPayer::class,
                'choice_label' => static function (BranchPayer $branchPayer): string {
                    return sprintf(
                        '%s / %s',
                        $branchPayer->getBranch()?->getName() ?? '',
                        $branchPayer->getPayer()?->getName() ?? ''
                    );
                },
                'placeholder' => 'Todos',
                'required' => false,
                'attr' => ['class' => 'form-select'],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('bp')
                        ->leftJoin('bp.branch', 'b')
                        ->addSelect('b')
                        ->leftJoin('bp.payer', 'p')
                        ->addSelect('p')
                        ->orderBy('b.name', 'ASC')
                        ->addOrderBy('p.name', 'ASC');
                },
            ])
            ->add('branchCareType', EntityType::class, [
                'label' => 'Tipo Atención/Sucursal',
                'class' => BranchCareType::class,
                'choice_label' => static function (BranchCareType $branchCareType): string {
                    return sprintf(
                        '%s - %s',
                        $branchCareType->getCareType()?->getName() ?? '',
                        $branchCareType->getBranch()?->getName() ?? ''
                    );
                },
                'placeholder' => 'Todos',
                'required' => false,
                'attr' => ['class' => 'form-select'],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('bct')
                        ->leftJoin('bct.careType', 'ct')
                        ->addSelect('ct')
                        ->leftJoin('bct.branch', 'b')
                        ->addSelect('b')
                        ->orderBy('ct.name', 'ASC')
                        ->addOrderBy('b.name', 'ASC');
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
            'data_class' => InsurancePlanPrice::class,
        ]);
    }
}
