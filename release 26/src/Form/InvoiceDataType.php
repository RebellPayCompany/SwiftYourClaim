<?php

namespace App\Form;

use App\Entity\InvoiceData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

class InvoiceDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'global.name',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('street', TextType::class, [
                'label' => 'global.street',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('houseNo', TextType::class, [
                'label' => 'global.house_no',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('apartmentNo', TextType::class, [
                'label' => 'global.house_no',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('zipCode', TextType::class, [
                'label' => 'global.zip_code',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'global.city',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('nip', TextType::class, [
                'label' => 'global.nip',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => InvoiceData::class,
        ]);
    }
}