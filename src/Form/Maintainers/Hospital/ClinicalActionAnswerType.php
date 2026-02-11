<?php

namespace App\Form\Maintainers\Hospital;

use App\Entity\Tenant\ClinicalActionAnswer;
use App\Entity\Tenant\ClinicalActionQuestion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ClinicalActionAnswerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Orden',
                'attr' => [
                    'min' => 0,
                ],
            ])
            ->add('preText', TextType::class, [
                'label' => 'Texto Previo',
                'required' => false,
            ])
            ->add('postText', TextType::class, [
                'label' => 'Texto Posterior',
                'required' => false,
            ])
            ->add('placeholder', TextType::class, [
                'label' => 'Placeholder',
                'required' => false,
            ])
            ->add('defaultValue', TextType::class, [
                'label' => 'Valor por Defecto',
                'required' => false,
            ])
            ->add('entityResponse', TextType::class, [
                'label' => 'Entidad Respuesta',
                'required' => false,
            ])
            ->add('isChecked', CheckboxType::class, [
                'label' => 'Seleccionado por Defecto',
                'required' => false,
            ])
            ->add('clinicalActionQuestion', EntityType::class, [
                'class' => ClinicalActionQuestion::class,
                'label' => 'Pregunta',
                'choice_label' => 'name',
                'placeholder' => 'Seleccione pregunta...',
                'required' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('q')
                        ->where('q.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('q.name', 'ASC');
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
            'data_class' => ClinicalActionAnswer::class,
        ]);
    }
}
