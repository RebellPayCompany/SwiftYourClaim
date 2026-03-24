<?php

namespace App\Form;

use App\Entity\RelatedEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

class ReportRelatedEntityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'global.entity_name',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('address', TextType::class, [
                'label' => 'global.address_headquarters',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('typeOtcRelated', TextType::class, [
                'label' => 'global.type_otc_related',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('phone', TextType::class, [
                'label' => 'global.phone_contact',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('email', TextType::class, [
                'required' => false,
                'label' => 'global.email',
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('businessAddress', TextType::class, [
                'label' => 'global.business_address',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('kni', TextType::class, [
                'label' => 'global.kni',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RelatedEntity::class,
        ]);
    }
}