<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\BranchPayer;
use App\Entity\Tenant\FeeCode;
use App\Entity\Tenant\FeeCodePrice;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class FeeCodePriceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('feeCode', EntityType::class, [
                'label' => 'Guarismo',
                'class' => FeeCode::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione guarismo',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank(['message' => 'El guarismo es obligatorio'])],
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
                'placeholder' => 'Seleccione',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank(['message' => 'La sucursal/financiador es obligatoria'])],
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
            ->add('effectiveDate', DateType::class, [
                'label' => 'Fecha Vigencia',
                'widget' => 'single_text',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [new Assert\NotBlank(['message' => 'La fecha de vigencia es obligatoria'])],
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Valor',
                'currency' => 'CLP',
                'divisor' => 1,
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [new Assert\NotBlank(['message' => 'El valor es obligatorio'])],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FeeCodePrice::class,
        ]);
    }
}
