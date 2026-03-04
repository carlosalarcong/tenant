<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\BranchPayer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

class MassAdjustmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('branchPayer', EntityType::class, [
                'class' => BranchPayer::class,
                'label' => 'Sucursal/Financiador',
                'choice_label' => static function (BranchPayer $branchPayer): string {
                    return sprintf(
                        '%s / %s',
                        $branchPayer->getBranch()?->getName() ?? '',
                        $branchPayer->getPayer()?->getName() ?? ''
                    );
                },
                'placeholder' => 'Seleccione',
                'query_builder' => static function ($repository) {
                    return $repository->createQueryBuilder('bp')
                        ->leftJoin('bp.branch', 'b')
                        ->addSelect('b')
                        ->leftJoin('bp.payer', 'p')
                        ->addSelect('p')
                        ->orderBy('b.name', 'ASC')
                        ->addOrderBy('p.name', 'ASC');
                },
            ])
            ->add('effectiveDate', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Fecha vigencia',
            ])
            ->add('adjustmentPercentageInput', NumberType::class, [
                'mapped' => false,
                'scale' => 2,
                'label' => '% de ajuste (positivo=aumento, negativo=reducción)',
                'attr' => [
                    'step' => '0.01',
                    'placeholder' => 'Ej: 10 o -5',
                ],
            ]);
    }
}
