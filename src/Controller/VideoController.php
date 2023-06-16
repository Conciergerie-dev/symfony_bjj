<?php

namespace App\Controller;

use App\Entity\Video;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VideoController extends AbstractController
{
    #[Route('/app', name: 'dashboard', methods: ['GET'])]
    public function index(VideoRepository $videoRepository): Response
    {
        return $this->render('video/index.html.twig', [
            'videos' => $videoRepository->findAll(),
            'tab' => 'dashboard',
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
