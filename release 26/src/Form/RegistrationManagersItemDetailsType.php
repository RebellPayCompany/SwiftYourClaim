<?php

namespace App\Form;

use App\Entity\UserManager;
use App\Validator\Constraints\PeselConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RegistrationManagersItemDetailsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pesel', TextType::class, [
                'label' => 'global.pesel',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new PeselConstraint()
                ]
            ])
            ->add('position', TextType::class, [
                'label' => 'global.position',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'global.phone',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserManager::class,
        ]);
    }
}