<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\OpenPlanDistribution;
use App\Entity\Tenant\SurgeryFeeItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class OpenPlanDistributionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('surgeryFeeItem', EntityType::class, [
                'label' => 'Ítem Equipo Médico',
                'class' => SurgeryFeeItem::class,
                'choice_label' => 'name',
                'placeholder' => 'Seleccione ítem',
                'attr' => ['class' => 'form-select'],
                'constraints' => [new Assert\NotBlank(['message' => 'El ítem es obligatorio'])],
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('sfi')
                        ->where('sfi.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('sfi.name', 'ASC');
                },
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Monto/Porcentaje',
                'currency' => 'CLP',
                'divisor' => 1,
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [new Assert\NotBlank(['message' => 'El monto es obligatorio'])],
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
            'data_class' => OpenPlanDistribution::class,
        ]);
    }
}
