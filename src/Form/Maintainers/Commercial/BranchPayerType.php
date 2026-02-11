<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\Branch;
use App\Entity\Tenant\BranchPayer;
use App\Entity\Tenant\Payer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BranchPayerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('branch', EntityType::class, [
                'label' => 'Sucursal',
                'class' => Branch::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione una sucursal',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank(['message' => 'La sucursal es obligatoria'])],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('b')
                        ->where('b.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('b.name', 'ASC');
                }
            ])
            ->add('payer', EntityType::class, [
                'label' => 'Financiador',
                'class' => Payer::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione un financiador',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank(['message' => 'El financiador es obligatorio'])],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('p')
                        ->where('p.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('p.name', 'ASC');
                }
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BranchPayer::class,
        ]);
    }
}
