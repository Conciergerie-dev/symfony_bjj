<?php

namespace App\Form;

use App\Entity\Playlist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Video;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class PlaylistFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name')
        ->add('description')
        ->add('thumbnail', FileType::class, [  //C'est pour upload img thumbnail
            'label' => 'Add New Image',

            // unmapped means that this field is not associated to any entity property
            'mapped' => false,

            // make it optional so you don't have to re-upload the PDF file
            // every time you edit the Product details
            'required' => false,

            // unmapped fields can't define their validation using annotations
            // in the associated entity, so you can use the PHP constraint classes
            'constraints' => [
                new File([
                    'maxSize' => '10240k',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png'
                    ],
                    'mimeTypesMessage' => 'Please upload a valid PNG or JPEG',
                ])
            ],
        ])
        ->add('videos', EntityType::class, [
            'class' => Video::class,
        
            // uses the User.username property as the visible option string
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
        ]); 
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Playlist::class,
        ]);
    }
}
