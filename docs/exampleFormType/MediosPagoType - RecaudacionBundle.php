<?php

namespace Rebsol\RecaudacionBundle\Form\Type\Recaudacion\Pago;

use Doctrine\ORM\EntityRepository;
use Rebsol\HermesBundle\Entity\Banco;
use Rebsol\HermesBundle\Entity\CondicionPago;
use Rebsol\HermesBundle\Entity\MotivoGratuidad;
use Rebsol\HermesBundle\Entity\TarjetaCredito;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as constraints;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
/**
 * @author ovaldenegro
 * @version 1.0.0
 * Fecha Creación: 05/11/2013
 * Participantes:
 */
class MediosPagoType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $listadoOtrosMediosPago = $options['idFromOtros'];
        foreach ($listadoOtrosMediosPago as $idFormOtros) {
            $builder
                ->add('monto_' . $idFormOtros, NumberType::class, array(
                        'mapped' => false,
                        'required' => true,
                    )
                )
                ->add('folio_' . $idFormOtros, NumberType::class, array(
                        'mapped' => false,
                        'required' => true,
                    )
                );

        }

        if ($options['clone']) {
            $ListadoMediosPago = array();
            $ListadoMediosPago[] = $options['idFrom'];
        } else {
            $ListadoMediosPago = $options['idFrom'];
        }
        $max = $options['idCantidad'];

        $idCantidad = $options['idCantidad'];
        foreach ($ListadoMediosPago as $idForm) {
            $builder
                ->add('idGratuidad_' . $idForm, EntityType::class, array(
                        'class' => MotivoGratuidad::class,
                        'choice_label' => 'nombre',
                        'required' => true,
                        'mapped' => false,
                        'auto_initialize' => false,
                        'placeholder' => 'Seleccionar Gratuidad',
                        'query_builder' => function (EntityRepository $repository) use ($options) {
                            return $repository->createQueryBuilder('s')
                                ->Where('s.idEstado = ?2')
                                ->andwhere('s.idSucursal = ?1')
                                ->setParameter(1, $options['sucursal'])
                                ->setParameter(2, $options['estado_activado']);
                        }
                    )
                )
                ->add('exedente_' . $idForm, NumberType::class, array(
                        'mapped' => false,
                        'required' => false,
                    )
                )
                ->add('medioPago_' . $idForm, NumberType::class, array(
                        'mapped' => false,
                        'required' => true,
                    )
                )
                ->add('dinamico_' . $idForm, NumberType::class, array(
                        'mapped' => false,
                        'required' => true,
                        'data' => 1,
                    )
                );

            if ($options['nuevo']) {
                for ($i = 0; $i <= $max; $i++) {

                    $builder
                        ->add('TarjetaDebito__' . $idForm . '_' . $i, EntityType::class, array(
                                'class' => Banco::class,
                                'choice_label' => 'nombre',
                                'required' => true,
                                'mapped' => false,
                                'auto_initialize' => false,
                                'placeholder' => 'Seleccionar Banco',
                                'query_builder' => function (EntityRepository $repository) use ($options) {
                                    return $repository->createQueryBuilder('s')
                                        ->where('s.idEmpresa = ?1')
                                        ->andWhere('s.idEstado = ?2')
                                        ->setParameter(1, $options['iEmpresa'])
                                        ->setParameter(2, $options['estado_activado']);
                                }
                            )
                        )
                        ->add('TarjetaCredito_' . $idForm . '_' . $i, EntityType::class, array(
                                'class' => TarjetaCredito::class,
                                'choice_label' => 'nombre',
                                'required' => true,
                                'mapped' => false,
                                'auto_initialize' => false,
                                'placeholder' => 'Seleccionar Tarjeta',
                                'query_builder' => function (EntityRepository $repository) use ($options) {
                                    return $repository->createQueryBuilder('t')
                                        ->join('t.idTarjetaCreditoTipo', 'tct')
                                        ->where('tct.idEmpresa = ?1')
                                        ->andWhere('tct.idEstado = ?2')
                                        ->andWhere('t.idEstado = ?2')
                                        ->orderBy('t.nombre', 'ASC')
                                        ->setParameter(1, $options['iEmpresa'])
                                        ->setParameter(2, $options['estado_activado']);
                                }
                            )
                        )
                        ->add('monto_' . $idForm . '_' . $i, NumberType::class, array(
                                'mapped' => false,
                                'required' => true,
                            )
                        )
                        ->add('voucher_' . $idForm . '_' . $i, TextType::class, array(
                                'mapped' => false,
                                'required' => true,
                            )
                        )
                        ->add('bono_' . $idForm . '_' . $i, NumberType::class, array(
                                'mapped' => false,
                                'required' => true,
                            )
                        )
                        ->add('rut_' . $idForm . '_' . $i, TextType::class, array(
                                'mapped' => false,
                                'required' => true,
                            )
                        )
                        ->add('nombre_' . $idForm . '_' . $i, TextType::class, array(
                                'mapped' => false,
                                'required' => true,
                            )
                        )
                        ->add('cheque_' . $idForm . '_' . $i, NumberType::class, array(
                                'mapped' => false,
                                'required' => true,
                            )
                        )
                        ->add('Bonificacion_' . $idForm . '_' . $i, NumberType::class, array(
                                'mapped' => false,
                                'required' => true,
                            )
                        )
                        ->add('Seguro_' . $idForm . '_' . $i, NumberType::class, array(
                                'mapped' => false,
                                'required' => true,
                            )
                        )
                        ->add('copago_' . $idForm . '_' . $i, NumberType::class, array(
                                'mapped' => false,
                                'required' => true,
                            )
                        )
                        ->add('banco_' . $idForm . '_' . $i, EntityType::class, array(
                                'class' => Banco::class,
                                'choice_label' => 'nombre',
                                'required' => true,
                                'mapped' => false,
                                'auto_initialize' => false,
                                'placeholder' => 'Seleccionar Banco',
                                'query_builder' => function (EntityRepository $repository) use ($options) {
                                    return $repository->createQueryBuilder('s')
                                        ->where('s.idEmpresa = ?1')
                                        ->andWhere('s.idEstado = ?2')
                                        ->setParameter(1, $options['iEmpresa'])
                                        ->setParameter(2, $options['estado_activado']);
                                }
                            )
                        )
                        ->add('condicion_' . $idForm . '_' . $i, EntityType::class, array(
                                'class' => CondicionPago::class,
                                'choice_label' => 'nombre',
                                'required' => true,
                                'mapped' => false,
                                'auto_initialize' => false,
                                'placeholder' => 'Seleccionar Condición',
                                'query_builder' => function (EntityRepository $repository) use ($options) {
                                    return $repository->createQueryBuilder('s')
                                        ->Where('s.idEstado = ?2')
                                        ->andWhere('s.idEmpresa = ?1')
                                        ->setParameter(1, $options['iEmpresa'])
                                        ->setParameter(2, $options['estado_activado']);
                                }
                            )
                        )
                        ->add('fecha_cheque_' . $idForm . '_' . $i, TextType::class, array(
                              'required'        => true,
                              'mapped'          => false,
                              'auto_initialize' => false,
                              'label'           => 'Fecha del Cheque',
                              'data'     => date('d/m/Y'),
                              'constraints' => array(new constraints\NotBlank(array('message' => 'Debe ingresar una Fecha')))
                            )
                        )
                        ->add('nombreTarjeta_' . $idForm . '_' . $i, TextType::class, array(
                                'mapped' => false,
                                'required' => true,
                            )
                        )
                        ->add('codAutorizacion_' . $idForm . '_' . $i, TextType::class, array(
                                'mapped' => false,
                                'required' => true,
                            )
                        )
                        ->add('ultimos4Numeros_' . $idForm . '_' . $i, TextType::class, array(
                                'mapped' => false,
                                'required' => true,
                            )
                        )
                        ->add('tarjetaTipo_' . $idForm . '_' . $i, TextType::class, array(
                                'mapped' => false,
                                'required' => true,
                            )
                        )
                        ->add('validaBonoOculto_' . $idForm . '_' . $i, HiddenType::class, array(
                                'required' => false,
                            ));
                }
            } else {

                $builder
                    ->add('TarjetaDebito__' . $idForm . '_' . $idCantidad, EntityType::class, array(
                            'class' => Banco::class,
                            'choice_label' => 'nombre',
                            'required' => true,
                            'mapped' => false,
                            'auto_initialize' => false,
                            'placeholder' => 'Seleccionar Banco',
                            'query_builder' => function (EntityRepository $repository) use ($options) {
                                return $repository->createQueryBuilder('s')
                                    ->where('s.idEmpresa = ?1')
                                    ->andWhere('s.idEstado = ?2')
                                    ->setParameter(1, $options['iEmpresa'])
                                    ->setParameter(2, $options['estado_activado']);
                            }
                        )
                    )
                    ->add('TarjetaCredito_' . $idForm . '_' . $idCantidad, EntityType::class, array(
                            'class' => TarjetaCredito::class,
                            'choice_label' => 'nombre',
                            'required' => true,
                            'mapped' => false,
                            'auto_initialize' => false,
                            'placeholder' => 'Seleccionar Tarjeta',
                            'query_builder' => function (EntityRepository $repository) use ($options) {
                                return $repository->createQueryBuilder('t')
                                    ->join('t.idTarjetaCreditoTipo', 'tct')
                                    ->where('tct.idEmpresa = ?1')
                                    ->andWhere('tct.idEstado = ?2')
                                    ->andWhere('t.idEstado = ?2')
                                    ->orderBy('t.nombre', 'ASC')
                                    ->setParameter(1, $options['iEmpresa'])
                                    ->setParameter(2, $options['estado_activado']);
                            }
                        )
                    )
                    ->add('monto_' . $idForm . '_' . $idCantidad, NumberType::class, array(
                            'mapped' => false,
                            'required' => true,
                        )
                    )
                    ->add('voucher_' . $idForm . '_' . $idCantidad, TextType::class, array(
                            'mapped' => false,
                            'required' => true,
                        )
                    )
                    ->add('bono_' . $idForm . '_' . $idCantidad, NumberType::class, array(
                            'mapped' => false,
                            'required' => true,
                        )
                    )
                    ->add('rut_' . $idForm . '_' . $idCantidad, TextType::class, array(
                            'mapped' => false,
                            'required' => true,
                        )
                    )
                    ->add('nombre_' . $idForm . '_' . $idCantidad, TextType::class, array(
                            'mapped' => false,
                            'required' => true,
                        )
                    )
                    ->add('cheque_' . $idForm . '_' . $idCantidad, NumberType::class, array(
                            'mapped' => false,
                            'required' => true,
                        )
                    )
                    ->add('Bonificacion_' . $idForm . '_' . $idCantidad, NumberType::class, array(
                            'mapped' => false,
                            'required' => true,
                        )
                    )
                    ->add('Seguro_' . $idForm . '_' . $idCantidad, NumberType::class, array(
                            'mapped' => false,
                            'required' => true,
                        )
                    )
                    ->add('copago_' . $idForm . '_' . $idCantidad, NumberType::class, array(
                            'mapped' => false,
                            'required' => true,
                        )
                    )
                    ->add('banco_' . $idForm . '_' . $idCantidad, EntityType::class, array(
                            'class' => Banco::class,
                            'choice_label' => 'nombre',
                            'required' => true,
                            'mapped' => false,
                            'auto_initialize' => false,
                            'placeholder' => 'Seleccionar Banco',
                            'query_builder' => function (EntityRepository $repository) use ($options) {
                                return $repository->createQueryBuilder('s')
                                    ->where('s.idEmpresa = ?1')
                                    ->andWhere('s.idEstado = ?2')
                                    ->setParameter(1, $options['iEmpresa'])
                                    ->setParameter(2, $options['estado_activado']);
                            }
                        )
                    )
                    ->add('condicion_' . $idForm . '_' . $idCantidad, EntityType::class, array(
                            'class' => CondicionPago::class,
                            'choice_label' => 'nombre',
                            'required' => true,
                            'mapped' => false,
                            'auto_initialize' => false,
                            'placeholder' => 'Seleccionar Condición',
                            'query_builder' => function (EntityRepository $repository) use ($options) {
                                return $repository->createQueryBuilder('s')
                                    ->Where('s.idEstado = ?2')
                                    ->andWhere('s.idEmpresa = ?1')
                                    ->setParameter(1, $options['iEmpresa'])
                                    ->setParameter(2, $options['estado_activado']);
                            }
                        )
                    )
                    ->add('fecha_cheque_' . $idForm . '_' . $idCantidad, TextType::class, array(
                            'required'        => true,
                            'mapped'          => false,
                            'auto_initialize' => false,
                            'label'           => 'Fecha del Cheque',
                            'data'            => date('d/m/Y'),
                            'constraints'      => array(new constraints\NotBlank(array('message' => 'Debe ingresar una Fecha')))
                        )
                    )
                    ->add('nombreTarjeta_' . $idForm . '_' . $idCantidad, TextType::class, array(
                            'mapped' => false,
                            'required' => true,
                        )
                    )
                    ->add('codAutorizacion_' . $idForm . '_' . $idCantidad, TextType::class, array(
                            'mapped' => false,
                            'required' => true,
                        )
                    )
                    ->add('ultimos4Numeros_' . $idForm . '_' . $idCantidad, TextType::class, array(
                            'mapped' => false,
                            'required' => true,
                        )
                    )
                    ->add('tarjetaTipo_' . $idForm . '_' . $idCantidad, TextType::class, array(
                            'mapped' => false,
                            'required' => true,
                        )
                    )

                    ->add('validaBonoOculto_' . $idForm . '_' . $idCantidad, HiddenType::class, array(
                        'required' => false,
                    ))
                    ;

            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {

        $resolver->setDefaults(array(
                'validaform' => true,
                'iEmpresa' => null,
                'idFrom' => null,
                'idFromOtros' => null,
                'clone' => false,
                'nuevo' => false,
                'sucursal' => null,
                'idCantidad' => null,
                'estado_activado' => null
            )
        );
    }

    public function getBlockPrefix()
    {

        return 'rebsol_hermesbundle_MediosPagoType';
    }

}
