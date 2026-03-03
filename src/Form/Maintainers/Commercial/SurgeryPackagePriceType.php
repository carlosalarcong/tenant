<?php

namespace App\Form\Maintainers\Commercial;

use App\Entity\Tenant\SurgeryFeeItem;
use App\Entity\Tenant\SurgeryPackagePrice;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SurgeryPackagePriceType extends AbstractType
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
            ->add('payerPrice', MoneyType::class, [
                'label' => 'Precio Isapre',
                'currency' => 'CLP',
                'divisor' => 1,
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [new Assert\NotBlank(['message' => 'El precio Isapre es obligatorio'])],
            ])
            ->add('clinicPrice', MoneyType::class, [
                'label' => 'Precio Clínica',
                'currency' => 'CLP',
                'divisor' => 1,
                'required' => true,
                'attr' => ['class' => 'form-control'],
                'constraints' => [new Assert\NotBlank(['message' => 'El precio clínica es obligatorio'])],
            ])
            ->add('effectiveDate', DateType::class, [
                'label' => 'F. Vigencia',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('isInUse', CheckboxType::class, [
                'label' => 'En Uso',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SurgeryPackagePrice::class,
        ]);
    }
}
