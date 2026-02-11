<?php

namespace App\Form;

use App\Entity\Tenant\MaintainerRolePermission;
use App\Entity\Tenant\Role;
use App\Security\Voter\MaintainerVoter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Form Type para crear/editar permisos de roles sobre mantenedores
 * 
 * CARACTERÍSTICAS:
 * - Roles cargados dinámicamente desde tabla 'role'
 * - Soporte para wildcards (*) que dan todos los permisos
 * - Validación de combinaciones únicas role+permission+category+maintainer
 * - Categorías opcionales para granularidad en Phase 2
 * - Campo maintainer opcional para granularidad específica en Phase 3
 * - Prioridad para resolver conflictos (mayor prioridad se evalúa primero)
 * 
 * @author Melisa Development Team
 * @since Sprint 2 - CRUD UI (Feb 2026)
 */
class MaintainerRolePermissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('role', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'name',
                'choice_value' => 'code',
                'label' => 'Rol',
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('r')
                        ->where('r.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('r.position', 'ASC');
                },
                'attr' => [
                    'class' => 'form-select',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'El rol es obligatorio']),
                ],
                'help' => 'Seleccione el rol al que aplicará el permiso (roles cargados desde tabla)',
                'placeholder' => 'Seleccione un rol...'
            ])
            ->add('permission', ChoiceType::class, [
                'label' => 'Permiso',
                'choices' => [
                    'Todos los permisos (*)' => '*',
                    'Crear (CREATE)' => MaintainerVoter::CREATE,
                    'Leer (READ)' => MaintainerVoter::READ,
                    'Actualizar (UPDATE)' => MaintainerVoter::UPDATE,
                    'Eliminar (DELETE)' => MaintainerVoter::DELETE,
                    'Exportar (EXPORT)' => MaintainerVoter::EXPORT,
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'El permiso es obligatorio']),
                ],
                'help' => 'Use * para dar todos los permisos al rol (wildcard)'
            ])
            ->add('granted', CheckboxType::class, [
                'label' => 'Concedido',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'help' => 'Si está marcado, el permiso es OTORGADO. Si no, es DENEGADO (usar con precaución)',
                'label_attr' => [
                    'class' => 'form-check-label'
                ]
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Categoría',
                'required' => false,
                'choices' => [
                    'Todas las categorías' => null,
                    'Básico' => 'basic',
                    'Clínico' => 'clinical',
                    'Comercial' => 'commercial',
                    'Hospitalario' => 'hospital',
                    'Recursos Humanos' => 'human',
                    'Talleres' => 'workshop',
                    'Liquidaciones' => 'settlements',
                    'Seguros' => 'insurance',
                    'Presupuesto' => 'budget',
                ],
                'placeholder' => 'Todas las categorías',
                'attr' => [
                    'class' => 'form-select',
                ],
                'help' => 'Opcional: restringe el permiso a una categoría específica de mantenedores (Phase 2)'
            ])
            ->add('maintainer', TextType::class, [
                'label' => 'Mantenedor Específico',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ej: App\\Entity\\Tenant\\Gender',
                    'maxlength' => 255
                ],
                'help' => 'Opcional: clase PHP completa del mantenedor específico (Phase 3). Deja vacío para aplicar a todos.',
                'constraints' => [
                    new Assert\Length([
                        'max' => 255,
                        'maxMessage' => 'El nombre del mantenedor no puede exceder {{ limit }} caracteres'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Descripción opcional del propósito de este permiso',
                    'rows' => 3
                ],
                'help' => 'Explica por qué existe este permiso para facilitar auditorías futuras'
            ])
            ->add('priority', IntegerType::class, [
                'label' => 'Prioridad',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 100,
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La prioridad es obligatoria']),
                    new Assert\Range([
                        'min' => 0,
                        'max' => 100,
                        'notInRangeMessage' => 'La prioridad debe estar entre {{ min }} y {{ max }}'
                    ])
                ],
                'help' => 'Mayor prioridad se evalúa primero. Default: 0. Wildcards (*) usan 10.'
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activo',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'help' => 'Solo permisos activos son considerados por el sistema',
                'label_attr' => [
                    'class' => 'form-check-label'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MaintainerRolePermission::class,
            'constraints' => [
                new Assert\Callback([$this, 'validateUniqueConstraint'])
            ]
        ]);
    }

    /**
     * Valida que la combinación role+permission+category+maintainer sea única
     * 
     * NOTA: Esta validación es básica. La restricción UNIQUE en base de datos
     * es la protección definitiva contra duplicados.
     */
    public function validateUniqueConstraint(MaintainerRolePermission $permission, ExecutionContextInterface $context): void
    {
        // Esta validación sería más completa consultando la base de datos,
        // pero por ahora confiamos en la constraint UNIQUE de la DB
        // que lanzará una excepción si hay duplicados
        
        // Validación adicional: advertir si se está creando un permiso DENY (granted=false)
        if (!$permission->isGranted()) {
            $context->buildViolation(
                'ADVERTENCIA: Está creando un permiso DENY (no concedido). ' .
                'Esto puede bloquear accesos inesperadamente. Use con precaución.'
            )
            ->atPath('granted')
            ->addViolation();
        }
        
        // Validación: wildcard (*) debe tener prioridad alta
        if ($permission->getPermission() === '*' && $permission->getPriority() < 5) {
            $context->buildViolation(
                'Los permisos wildcard (*) deberían tener prioridad >= 5 para asegurar que se evalúen correctamente.'
            )
            ->atPath('priority')
            ->addViolation();
        }
    }
}
