<?php

namespace App\Form\Maintainers\Treasury;

use App\Entity\Tenant\CashRegisterLocation;
use App\Entity\Tenant\SubCompany;
use App\Entity\Tenant\Voucher;
use App\Repository\Tenant\CashRegisterLocationRepository;
use App\Repository\Tenant\SubCompanyRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VoucherType extends AbstractType
{
    public function __construct(
        private readonly CashRegisterLocationRepository $locationRepository,
        private readonly SubCompanyRepository $subCompanyRepository,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cashRegisterLocation', EntityType::class, [
                'class'         => CashRegisterLocation::class,
                'choice_label'  => 'name',
                'label'         => 'Ubicación de caja',
                'placeholder'   => 'Seleccione una ubicación',
                'query_builder' => function () {
                    return $this->locationRepository->createQueryBuilder('crl')
                        ->where('crl.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('crl.name', 'ASC');
                },
            ])
            ->add('subCompany', EntityType::class, [
                'class'        => SubCompany::class,
                'choice_label' => 'name',
                'label'        => 'Sub-empresa',
                'placeholder'  => 'Sin sub-empresa (opcional)',
                'required'     => false,
                'query_builder' => function () {
                    return $this->subCompanyRepository->createQueryBuilder('sc')
                        ->orderBy('sc.name', 'ASC');
                },
            ])
            ->add('folioFrom', IntegerType::class, [
                'label' => 'Folio inicial',
                'attr'  => ['min' => 1, 'placeholder' => 'Ej: 1'],
            ])
            ->add('folioTo', IntegerType::class, [
                'label' => 'Folio final',
                'attr'  => ['min' => 1, 'placeholder' => 'Ej: 500'],
            ])
            ->add('currentFolio', IntegerType::class, [
                'label' => 'Folio actual (próximo a usar)',
                'attr'  => ['min' => 0],
                'help'  => 'Normalmente coincide con el folio inicial. No editar manualmente si hay folios consumidos.',
            ])
            ->add('isActive', CheckboxType::class, [
                'label'    => 'Activo',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Voucher::class,
        ]);
    }
}
