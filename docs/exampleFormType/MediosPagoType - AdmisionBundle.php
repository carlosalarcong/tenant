<?php

namespace Rebsol\AdmisionBundle\Form\Type;

use Rebsol\HermesBundle\Entity\Banco;
use Rebsol\HermesBundle\Entity\CondicionPago;
use Rebsol\HermesBundle\Entity\MotivoGratuidad;
use Rebsol\HermesBundle\Entity\TarjetaCredito;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as validaform;

/**
 * @author GQuinteros
 * @version 1.0.0
 * Fecha Creación: 05/11/2013
 * Participantes:
 */
class MediosPagoType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $listadoOtrosMediosPago = $options['idFromOtros'];
        //dump($listadoOtrosMediosPago);exit;
        foreach ($listadoOtrosMediosPago as $idFormOtros) {

            $builder
                ->add('monto_' . $idFormOtros, NumberType::class, array(
                        'mapped' => false,
                        'required' => false)
                )
                ->add('folio_' . $idFormOtros, NumberType::class, array(
                    'mapped' => false,
                    'required' => true,
                ))
                ->add('folioGarantia_' . $idFormOtros, HiddenType::class, array(
                    'mapped' => false,
                    'required' => false,
                ))
                ->add('idGratuidad_' . $idFormOtros, EntityType::class, array(
                        'class' => MotivoGratuidad::class,
                        'choice_label'=> 'nombre',
                        'required' => true,
                        'mapped' => false,
                        'em' => $options['database_default'],
                        'placeholder' => 'Seleccionar Gratuidad',
                        'query_builder' => function (\Doctrine\ORM\EntityRepository $repository) use ($options) {
                            return $repository->createQueryBuilder('s')
                                ->Where('s.idEstado = ?2')
                                ->andwhere('s.idSucursal = ?1')
                                ->setParameter(1, $options['sucursal'])
                                ->setParameter(2, $options['estado_activado']);
                        })
                );
        }


        if ($options['clone']) {
            $listadoMediosPago = array();
            $listadoMediosPago[] = $options['idFrom'];
        } else {
            $listadoMediosPago = $options['idFrom'];
        }
        $max = 20;
        $idCantidad = $options['idCantidad'];

        foreach ($listadoMediosPago as $idForm) {

            if (!$options['nuevo']) {
                for ($i = 0; $i <= $max; $i++) {

                    $builder
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
                        )
                        ->add('monto_' . $idForm . '_' . $i, NumberType::class, array(
                                'mapped' => false,
                                'required' => false,
                            )
                        )
                        ->add('folioGarantia_' . $idForm . '_' . $i, HiddenType::class, array(
                                'mapped' => false,
                                'required' => false,
                            )
                        )
                        ->add('voucher_' . $idForm . '_' . $i, NumberType::class, array(
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
                                'choice_label'=> 'nombre',
                                'required' => true,
                                'mapped' => false,
                                'em' => $options['database_default'],
                                'placeholder' => 'Seleccionar Banco',
                                'query_builder' => function (\Doctrine\ORM\EntityRepository $repository) use ($options) {
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
                                'choice_label'=> 'nombre',
                                'required' => true,
                                'mapped' => false,
                                'em' => $options['database_default'],
                                'placeholder' => 'Seleccionar Condición',
                                'query_builder' => function (\Doctrine\ORM\EntityRepository $repository) use ($options) {
                                    return $repository->createQueryBuilder('s')
                                        ->Where('s.idEstado = ?2')
                                        ->setParameter(2, $options['estado_activado']);
                                }
                            )
                        )
                        ->add('folio_' . $idForm . '_' . $i, NumberType::class, array(
                            'mapped' => false,
                            'required' => true,
                                )
                        );
                    $builder
                        ->add('idGratuidad_' . $idForm . '_' . $i, EntityType::class, array(
                                'class' => MotivoGratuidad::class,
                                'choice_label'=> 'nombre',
                                'required' => true,
                                'mapped' => false,
                                'em' => $options['database_default'],
                                'placeholder' => 'Seleccionar Gratuidad',
                                'query_builder' => function (\Doctrine\ORM\EntityRepository $repository) use ($options) {
                                    return $repository->createQueryBuilder('s')
                                        ->Where('s.idEstado = ?2')
                                        ->andwhere('s.idSucursal = ?1')
                                        ->setParameter(1, $options['sucursal'])
                                        ->setParameter(2, $options['estado_activado']);
                                })
                        )
                        ->add('TarjetaDebito__' . $idForm . '_' . $i, EntityType::class, array(
                                'class' => Banco::class,
                                'choice_label'=> 'nombre',
                                'required' => true,
                                'mapped' => false,
                                'em' => $options['database_default'],
                                'placeholder' => 'Seleccionar Debito',
                                'query_builder' => function (\Doctrine\ORM\EntityRepository $repository) use ($options) {
                                    return $repository->createQueryBuilder('t')
                                        ->where('t.idEmpresa = ?1')
                                        ->andWhere('t.idEstado = ?2')
                                        ->orderBy('t.nombre', 'ASC')
                                        ->setParameter(1, $options['iEmpresa'])
                                        ->setParameter(2, $options['estado_activado']);
                                })
                        )
                        ->add('TarjetaCredito_' . $idForm . '_' . $i, EntityType::class, array(
                                'class' => TarjetaCredito::class,
                                'choice_label'=> 'nombre',
                                'required' => true,
                                'mapped' => false,
                                'em' => $options['database_default'],
                                'placeholder' => 'Seleccionar Tarjeta',
                                'query_builder' => function (\Doctrine\ORM\EntityRepository $repository) use ($options) {
                                    return $repository->createQueryBuilder('t')
                                        ->join('t.idTarjetaCreditoTipo', 'tct')
                                        ->where('tct.idEmpresa = ?1')
                                        ->andWhere('tct.idEstado = ?2')
                                        ->andWhere('t.idEstado = ?2')
                                        ->orderBy('t.nombre', 'ASC')
                                        ->setParameter(1, $options['iEmpresa'])
                                        ->setParameter(2, $options['estado_activado']);
                                })
                        );
                }
            } else {
                $builder
                    ->add('exedente_' . $idForm, NumberType::class, array(
                            'mapped' => false,
                            'required' => false,
                        )
                    )
                    ->add('folioGarantia_' . $idForm . '_' . $idCantidad, HiddenType::class, array(
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
                    )
                    ->add('monto_' . $idForm . '_' . $idCantidad, NumberType::class, array(
                            'mapped' => false,
                            'required' => true,
                        )
                    )
                    ->add('voucher_' . $idForm . '_' . $idCantidad, NumberType::class, array(
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
                            'choice_label'=> 'nombre',
                            'required' => true,
                            'mapped' => false,
                            'em' => $options['database_default'],
                            'placeholder' => 'Seleccionar Banco',
                            'query_builder' => function (\Doctrine\ORM\EntityRepository $repository) use ($options) {
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
                            'choice_label'=> 'nombre',
                            'required' => true,
                            'mapped' => false,
                            'em' => $options['database_default'],
                            'placeholder' => 'Seleccionar Condición',
                            'query_builder' => function (\Doctrine\ORM\EntityRepository $repository) use ($options) {
                                return $repository->createQueryBuilder('s')
                                    ->Where('s.idEstado = ?2')
                                    ->andWhere('s.idEmpresa = ?1')
                                    ->setParameter(1, $options['iEmpresa'])
                                    ->setParameter(2, $options['estado_activado']);
                            }
                        )
                    )
                    ->add('folio_' . $idForm . '_' . $idCantidad, NumberType::class, array(
                        'mapped' => false,
                        'required' => true,
                            )
                    );                $builder
                    ->add('idGratuidad_' . $idForm . '_' . $idCantidad, EntityType::class, array(
                            'class' => MotivoGratuidad::class,
                            'choice_label'=> 'nombre',
                            'required' => true,
                            'mapped' => false,
                            'em' => $options['database_default'],
                            'placeholder' => 'Seleccionar Gratuidad',
                            'query_builder' => function (\Doctrine\ORM\EntityRepository $repository) use ($options) {
                                return $repository->createQueryBuilder('s')
                                    ->Where('s.idEstado = ?2')
                                    ->andwhere('s.idSucursal = ?1')
                                    ->setParameter(1, $options['sucursal'])
                                    ->setParameter(2, $options['estado_activado']);
                            })
                    )
                    ->add('TarjetaDebito__' . $idForm . '_' . $idCantidad, EntityType::class, array(
                            'class' => Banco::class,
                            'choice_label'=> 'nombre',
                            'required' => true,
                            'mapped' => false,
                            'em' => $options['database_default'],
                            'placeholder' => 'Seleccionar Debito',
                            'query_builder' => function (\Doctrine\ORM\EntityRepository $repository) use ($options) {
                                return $repository->createQueryBuilder('t')
                                    ->where('t.idEmpresa = ?1')
                                    ->andWhere('t.idEstado = ?2')
                                    ->orderBy('t.nombre', 'ASC')
                                    ->setParameter(1, $options['iEmpresa'])
                                    ->setParameter(2, $options['estado_activado']);
                            })
                    )
                    ->add('TarjetaCredito_' . $idForm . '_' . $idCantidad, EntityType::class, array(
                            'class' => TarjetaCredito::class,
                            'choice_label'=> 'nombre',
                            'required' => true,
                            'mapped' => false,
                            'em' => $options['database_default'],
                            'placeholder' => 'Seleccionar Tarjeta',
                            'query_builder' => function (\Doctrine\ORM\EntityRepository $repository) use ($options) {
                                return $repository->createQueryBuilder('t')
                                    ->join('t.idTarjetaCreditoTipo', 'tct')
                                    ->where('tct.idEmpresa = ?1')
                                    ->andWhere('tct.idEstado = ?2')
                                    ->andWhere('t.idEstado = ?2')
                                    ->orderBy('t.nombre', 'ASC')
                                    ->setParameter(1, $options['iEmpresa'])
                                    ->setParameter(2, $options['estado_activado']);
                            })
                    );

            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array
        (
            'validaform' => true,
            'iEmpresa' => null,
            'idFrom' => null,
            'idFromOtros' => null,
            'clone' => false,
            'nuevo' => false,
            'sucursal' => null,
            'idCantidad' => null,
            'estado_activado' => null,
            'database_default' => null
        ));
    }

    public function getBlockPrefix()
    {
        return 'Admision_MediosPagoType';
    }

}
