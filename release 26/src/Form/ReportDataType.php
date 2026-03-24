<?php

namespace App\Form;

use App\Entity\ReportData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints as Assert;

class ReportDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pesel', TextType::class, [
                'label' => 'global.pesel',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('noPesel', CheckboxType::class, [
                'label'    => 'global.dont_have_pesel',
                'required' => false,
            ])
            ->add('firstName', TextType::class, [
                'label' => 'global.first_name',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'global.last_name',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ],
            ])
            ->add('position', TextType::class, [
                'label' => 'global.position',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('address', TextType::class, [
                'label' => 'global.address',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
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
            'data_class' => ReportData::class,
        ]);
    }
}