<?php

namespace App\Form;

use App\Entity\Report;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('data', ReportDataType::class, [
                'label' => false,
            ])
            ->add('relatedEntity', CollectionType::class, [
                'label' => false,
                'entry_type' => ReportRelatedEntityType::class,
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true
            ])
            ->add('relatedPerson', CollectionType::class, [
                'label' => false,
                'entry_type' => ReportRelatedPersonType::class,
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true
            ])
            ->add('noRelatedEntity', CheckboxType::class, [
                'label'    => 'global.dont_have_entities_closely_related',
                'required' => false,
            ])
            ->add('noRelatedPerson', CheckboxType::class, [
                'label'    => 'global.dont_have_person_closely_related',
                'required' => false,
            ])
            ->add('sendToIssuer', HiddenType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'global.save_data',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Report::class,
        ]);
    }
}