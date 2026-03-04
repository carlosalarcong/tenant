<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\BranchPayer;
use App\Entity\Tenant\InsurancePlan;
use App\Repository\Tenant\BranchPayerRepository;
use App\Repository\Tenant\InsurancePlanRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class InsurancePlanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'constraints' => [new Assert\NotBlank()],
            ])
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
                'query_builder' => static function (BranchPayerRepository $repository) {
                    return $repository->createQueryBuilder('bp')
                        ->leftJoin('bp.branch', 'b')
                        ->addSelect('b')
                        ->leftJoin('bp.payer', 'p')
                        ->addSelect('p')
                        ->orderBy('b.name', 'ASC')
                        ->addOrderBy('p.name', 'ASC');
                },
                'required' => false,
                'placeholder' => 'Sin asignación',
            ])
            ->add('parentPlan', EntityType::class, [
                'class' => InsurancePlan::class,
                'label' => 'Plan padre',
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Sin plan padre',
                'query_builder' => static function (InsurancePlanRepository $repository) {
                    return $repository->createQueryBuilder('ip')
                        ->where('ip.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('ip.name', 'ASC');
                },
            ])
            ->add('isPackage', CheckboxType::class, [
                'label' => 'Es paquete',
                'required' => false,
            ])
            ->add('isTelemedicine', CheckboxType::class, [
                'label' => 'Es teleconsulta',
                'required' => false,
            ])
            ->add('isDisabled', CheckboxType::class, [
                'label' => 'Inhabilitado',
                'required' => false,
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InsurancePlan::class,
        ]);
    }
}
