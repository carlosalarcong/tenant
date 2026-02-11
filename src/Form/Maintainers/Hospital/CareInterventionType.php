<?php

namespace App\Form\Maintainers\Hospital;

use App\Entity\Tenant\CareCategory;
use App\Entity\Tenant\CareIntervention;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class CareInterventionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextType::class, [
                'label' => 'Descripcion',
                'attr' => [
                    'placeholder' => 'Descripcion del cuidado',
                ],
            ])
            ->add('careCategory', EntityType::class, [
                'class' => CareCategory::class,
                'label' => 'Categoria',
                'choice_label' => 'name',
                'placeholder' => 'Seleccione categoria...',
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
            'data_class' => CareIntervention::class,
        ]);
    }
}
