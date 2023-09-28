<?php

namespace App\Form;

use App\Entity\Video;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class OtherVideoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('category', ChoiceType::class, [
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'choices'  => [
                    'Nutrition' => 'Nutrition',
                    'Self Defense' => 'Self Defense',
                    'Conditionning' => 'Conditionning',
                    'Mobility' => 'Mobility',
                    'BJJ Talks' => 'BJJ Talks',
                ],
            ])
            ->add('free', CheckboxType::class, [
                'label'    => 'free content',
                'required' => false,
            ])
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
            ->add('video', FileType::class, [
                'label' => 'Add New Video',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '50M',
                        'mimeTypes' => [
                            'video/mp4',
                            'video/mpeg',
                            'video/quicktime',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid video (MP4, MPEG, or QuickTime)',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Video::class,
        ]);
    }
}
