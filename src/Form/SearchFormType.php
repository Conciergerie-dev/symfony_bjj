<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Service\PositionChoices;

class SearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('basePosition', ChoiceType::class, [
            'required' => true,
            'multiple' => false,
            'expanded' => false,
            'choices'  => PositionChoices::getBasePositions(),
        ])
        ->add('endingPosition', ChoiceType::class, [
            'required' => true,
            'multiple' => false,
            'expanded' => false,
            'choices'  => PositionChoices::getEndingPositions(),
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
