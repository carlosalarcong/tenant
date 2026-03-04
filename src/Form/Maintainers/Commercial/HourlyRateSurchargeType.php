<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\BranchPayer;
use App\Entity\Tenant\HourlyRateSurcharge;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HourlyRateSurchargeType extends AbstractType
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
            ->add('startTime', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Hora inicio',
            ])
            ->add('endTime', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Hora término',
            ])
            ->add('dayOfWeek', ChoiceType::class, [
                'choices' => [
                    'Lunes' => 0,
                    'Martes' => 1,
                    'Miércoles' => 2,
                    'Jueves' => 3,
                    'Viernes' => 4,
                    'Sábado' => 5,
                    'Domingo' => 6,
                ],
                'label' => 'Día',
            ])
            ->add('percentage', NumberType::class, [
                'scale' => 2,
                'label' => '% Recargo',
                'attr' => ['step' => '0.01'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HourlyRateSurcharge::class,
        ]);
    }
}
