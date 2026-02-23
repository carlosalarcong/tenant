<?php

namespace App\Form\Revenue\Payment\Method;

use App\Entity\Tenant\GratuityReason;
use App\Entity\Tenant\GratuityType;
use App\Repository\Tenant\GratuityReasonRepository;
use App\Repository\Tenant\GratuityTypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Gratuidad — exención total o parcial del cobro al paciente.
 * Los catálogos GratuityType y GratuityReason ya existen en el tenant.
 * Legacy: FormaDePago_Gratuidad.html.twig
 */
class GratuityPaymentType extends AbstractType
{
    public function __construct(
        private readonly GratuityTypeRepository $gratuityTypeRepository,
        private readonly GratuityReasonRepository $gratuityReasonRepository,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('gratuity_type_id', EntityType::class, [
                'class' => GratuityType::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'query_builder' => $this->gratuityTypeRepository->createQueryBuilder('gt')
                    ->where('gt.isActive = :active')
                    ->setParameter('active', true)
                    ->orderBy('gt.name', 'ASC'),
                'placeholder' => 'Seleccionar tipo',
                'required' => true,
            ])
            ->add('gratuity_reason_id', EntityType::class, [
                'class' => GratuityReason::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'query_builder' => $this->gratuityReasonRepository->createQueryBuilder('gr')
                    ->where('gr.isActive = :active')
                    ->setParameter('active', true)
                    ->orderBy('gr.name', 'ASC'),
                'placeholder' => 'Seleccionar motivo',
                'required' => true,
            ])
            ->add('amount', NumberType::class, [
                'required' => true,
                'scale' => 2,
                'html5' => true,
                'attr' => [
                    'step' => '0.01',
                    'min' => '0',
                    'data-payment-amount-input' => '1',
                    'placeholder' => '0',
                ],
            ])
            ->add('notes', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'rows' => 2,
                    'maxlength' => 500,
                    'placeholder' => 'Observaciones (opcional)',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
