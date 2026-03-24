<?php

namespace App\Form;

use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RegistrationManagersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('manager', CollectionType::class, [
                'label' => false,
                'entry_type' => RegistrationManagersItemType::class,
                'allow_add' => true,
                'entry_options' => [
                    'attr' => [
                        'class' => 'test'
                    ],
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'global.confirm',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}