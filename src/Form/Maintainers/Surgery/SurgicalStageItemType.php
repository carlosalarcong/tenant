<?php

namespace App\Form\Maintainers\Surgery;

use App\Entity\Tenant\SurgicalStage;
use App\Entity\Tenant\SurgicalStageItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurgicalStageItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Orden',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => ['class' => 'form-control', 'maxlength' => 255],
            ])
            ->add('alternatives', TextareaType::class, [
                'label' => 'Alternativas',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4, 'maxlength' => 2000],
            ])
            ->add('hasResponse', CheckboxType::class, [
                'label' => 'Tiene Respuesta',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('isMandatory', CheckboxType::class, [
                'label' => 'Obligatorio',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('surgicalStage', EntityType::class, [
                'class' => SurgicalStage::class,
                'choice_label' => 'name',
                'label' => 'Etapa Quirúrgica',
                'placeholder' => 'Seleccione una etapa',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('parent', EntityType::class, [
                'class' => SurgicalStageItem::class,
                'choice_label' => 'name',
                'label' => 'Item Padre',
                'required' => false,
                'placeholder' => 'Seleccione un item padre',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SurgicalStageItem::class,
        ]);
    }
}
