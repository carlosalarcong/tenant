<?php

namespace App\Form\Maintainers\Hospital;

use App\Entity\Tenant\ClinicalActionCategory;
use App\Entity\Tenant\ClinicalActionQuestion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ClinicalActionQuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nombre',
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Orden',
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('rangeMin', IntegerType::class, [
                'label' => 'Rango Minimo',
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('rangeMax', IntegerType::class, [
                'label' => 'Rango Maximo',
                'required' => false,
            ])
            ->add('ageMin', IntegerType::class, [
                'label' => 'Edad Minima',
                'required' => false,
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('ageMax', IntegerType::class, [
                'label' => 'Edad Maxima',
                'required' => false,
            ])
            ->add('helpText', TextType::class, [
                'label' => 'Texto Ayuda',
                'required' => false,
            ])
            ->add('fieldType', ChoiceType::class, [
                'label' => 'Tipo Campo',
                'required' => false,
                'choices' => [
                    'Texto' => 'text',
                    'Numero' => 'number',
                    'Seleccion' => 'select',
                    'Checkbox' => 'checkbox',
                ],
            ])
            ->add('isMultiple', CheckboxType::class, [
                'label' => 'Multiple',
                'required' => false,
            ])
            ->add('isExtended', CheckboxType::class, [
                'label' => 'Extendido',
                'required' => false,
            ])
            ->add('isRequired', CheckboxType::class, [
                'label' => 'Obligatorio',
                'required' => false,
            ])
            ->add('clinicalActionCategory', EntityType::class, [
                'class' => ClinicalActionCategory::class,
                'label' => 'Categoria',
                'choice_label' => 'name',
                'placeholder' => 'Seleccione...',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('c.name', 'ASC');
                },
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClinicalActionQuestion::class,
        ]);
    }
}
