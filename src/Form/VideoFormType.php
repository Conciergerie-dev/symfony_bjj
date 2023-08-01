<?php

namespace App\Form;

use App\Entity\Video;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
class VideoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('instructor', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('u')
                    ->where('u.roles LIKE :admin')
                    ->setParameter('admin', '%"'.'ROLE_ADMIN'.'"%')
                    ->orWhere('u.roles LIKE :instructor')
                    ->setParameter('instructor', '%"'.'ROLE_INSTRUCTOR'.'"%');
                },
                // uses the User.username property as the visible option string
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => false,
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
            'data_class' => Video::class,
        ]);
    }
}