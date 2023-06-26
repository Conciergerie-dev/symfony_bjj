<?php

namespace App\Controller;

use App\Entity\Video;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

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
    public function addVideo(Request $request): Response
    {
        $video = new Video(); //new instance
        
        $form = $this->createFormBuilder($video)
            ->add('name', TextType::class)
            ->add('description', TextareaType::class)
            ->add('thumbnail', TextType::class)
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager -> persist($video);
            $entityManager -> flush();

            return $this->redirectToRoute('saved_videos');
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
