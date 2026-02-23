<?php

namespace App\Form\Maintainers\Treasury;

use App\Entity\Tenant\CashierAssignment;
use App\Entity\Tenant\CashRegisterLocation;
use App\Entity\Tenant\Member;
use App\Repository\Tenant\CashRegisterLocationRepository;
use App\Repository\Tenant\MemberRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CashierAssignmentType extends AbstractType
{
    public function __construct(
        private readonly MemberRepository $memberRepository,
        private readonly CashRegisterLocationRepository $cashRegisterLocationRepository,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('member', EntityType::class, [
                'class'         => Member::class,
                'choice_label'  => 'username',
                'label'         => 'Cajero',
                'placeholder'   => 'Seleccione un cajero',
                'query_builder' => function () {
                    return $this->memberRepository->createQueryBuilder('m')
                        ->where('m.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('m.username', 'ASC');
                },
            ])
            ->add('cashRegisterLocation', EntityType::class, [
                'class'         => CashRegisterLocation::class,
                'choice_label'  => 'name',
                'label'         => 'Ubicación de caja',
                'placeholder'   => 'Seleccione una ubicación',
                'query_builder' => function () {
                    return $this->cashRegisterLocationRepository->createQueryBuilder('crl')
                        ->where('crl.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('crl.name', 'ASC');
                },
            ])
            ->add('isActive', CheckboxType::class, [
                'label'    => 'Activo',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CashierAssignment::class,
        ]);
    }
}
