<?php

namespace App\Form\Admission;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ForeignPatientType extends PatientRegistrationType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('foreign_mode', true);
    }
}
