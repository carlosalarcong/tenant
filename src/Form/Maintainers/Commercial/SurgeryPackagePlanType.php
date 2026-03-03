<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\BranchPayer;
use App\Entity\Tenant\SurgeryPackagePlan;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SurgeryPackagePlanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre del Plan',
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [new Assert\NotBlank(['message' => 'El nombre del plan es obligatorio'])],
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
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SurgeryPackagePlan::class,
        ]);
    }
}
