<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints as Assert;

class ManagerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'global.email',
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\Email(),
                ]
            ])
            ->add('manager', ManagerDetailsType::class, [
                'label' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'global.create_account',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);

        if (!$options['access']) {
            $builder->add('access', CheckboxType::class, [
                'label' => 'global.connect_to_manager_account',
                'required' => false,
                'mapped' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'access' => null
        ]);
    }
}