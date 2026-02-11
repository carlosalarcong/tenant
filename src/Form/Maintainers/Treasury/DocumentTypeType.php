<?php

namespace App\Form\Maintainers\Treasury;

use App\Entity\Tenant\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('siiCode', TextType::class, [
                'label' => 'Código SII',
                'required' => false,
                'attr' => [
                    'maxlength' => 3,
                    'placeholder' => 'Código del SII (opcional)'
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nombre',
                'attr' => [
                    'maxlength' => 70,
                    'placeholder' => 'Ingrese el nombre del tipo de documento'
                ]
            ])
            ->add('isDte', CheckboxType::class, [
                'label' => '¿Es DTE?',
                'required' => false,
            ])
            ->add('isLogistics', CheckboxType::class, [
                'label' => '¿Es de Logística?',
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
            'data_class' => DocumentType::class,
        ]);
    }
}
