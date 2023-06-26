<?php

namespace App\Controller;
//D'abbord en 'use'
//Après en paramettre de mes methodes
// Après je peux me servir dans me méthodes
use App\Entity\Video;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\String\Slugger\SluggerInterface;

class VideoController extends AbstractController
{
    #[Route('/app', name: 'dashboard', methods: ['GET'])]
    public function index(VideoRepository $videoRepository): Response
    {
        return $this->render('video/index.html.twig', [
            'videos' => $videoRepository->findAll(),
            'tab' => 'dashboard|add_video',
        ]);
    }
    // Adding videos
    #[Route('/app/add-video', name: 'add_video', methods: ['GET', 'POST'])]
    public function addVideo(Request $request, PersistenceManagerRegistry $doctrine, SluggerInterface $slugger ): Response
    {
        $video = new Video(); //new instance
        
        $form = $this->createFormBuilder($video)
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            // ->add('thumbnail', TextType::class)
            //C'est pour upload img thumbnail
            ->add('thumbnail', FileType::class, [
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
            ->add('save', SubmitType::class, ['label' => 'add_video'])
            ->getForm();

        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $thumbnailFile = $form->get('thumbnail')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($thumbnailFile) {
                $originalFilename = pathinfo($thumbnailFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$thumbnailFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $thumbnailFile->move(
                        $this->getParameter('thumbnail_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                // ça viens du set de ma entity(video.php)
                $video->setThumbnail($newFilename);
                
                $entityManager = $doctrine->getManager();
                $entityManager -> persist($video);
                $entityManager -> flush();

                return $this->redirectToRoute('dashboard');
            }
        }
        return $this->render('video/add.html.twig',[
            'form' => $form->createView(),
            'tab' => 'add_video',
        ]);
    }
    
    #[Route('/app/saved', name: 'saved_videos', methods: ['GET'])]
    public function saved(): Response
    {
        $liked = $this->getUser()->getLiked()->toArray();
        return $this->render('video/index.html.twig', [
            'videos' => $liked,
            'tab' => 'saved_videos',
        ]);
    }

    /*public function addFavorite(Video $video): void {
        $this->getUser()->addLiked($video);
        dd($this->getUser()->getLiked()->toArray());
    }*/
}
