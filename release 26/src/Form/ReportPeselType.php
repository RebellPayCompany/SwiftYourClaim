<?php

namespace App\Form;

use App\Entity\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ReportPeselType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pesel', TextType::class, [
                'label' => 'global.pesel',
                'attr' => [
                    'class' => 'form-control',
                ],
                'property_path' => 'data.pesel',
                'data' => $options['pesel']
            ])
            ->add('noPesel', CheckboxType::class, [
                'label'    => 'global.dont_have_pesel',
                'required' => false,
                'property_path' => 'data.noPesel',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'global.get_data_from_krs',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Report::class,
            'pesel' => null
        ]);
    }
}