<?php

namespace App\Form;

use App\Entity\Lesson;
use App\Entity\User;
use App\Entity\Video;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LessonFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('date', DateType::class, [
            'widget' => 'single_text'
        ])
        ->add('time', TimeType::class, [
            'widget' => 'single_text'
        ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'name',
                'multiple' => true,
            ])
            ->add('videos', EntityType::class, [
                'class' => Video::class,
                'choice_label' => 'name',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
        ]);
    }
}
