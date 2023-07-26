<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('basePosition', ChoiceType::class, [
            'required' => true,
            'multiple' => false,
            'expanded' => false,
            'choices'  => [
                'Top Closed Guard' => 'Top Closed Guard',
                'Top Mount' => 'Top Mount',
                'Top Side Control' => 'Top Side Control',
                'Bottom Closed Guard' => 'Bottom Closed Guard',
                'Bottom Mount' => 'Bottom Mount',
                'Bottom Side Control' => 'Bottom Side Control',
                'Other' => 'Other',
            ],
        ])
        ->add('endingPosition', ChoiceType::class, [
            'required' => true,
            'multiple' => false,
            'expanded' => false,
            'choices'  => [
                'Submission' => 'Submission',
                'Defense' => 'Defense',
                'Other' => 'Other',
            ],
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
