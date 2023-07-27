<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\OtherVideoFormType;
use App\Repository\VideoRepository;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use App\Form\OtherVideoSearchFormType;

class OtherVideoController extends AbstractController
{
    #[Route('/other/video', name: 'app_other_video')]
    public function index(VideoRepository $videoRepository): Response
    {
        $videos = $videoRepository->findOtherVideo();

        return $this->render('other_video/index.html.twig', [
            'videos' => $videos,
        ]);
    }

    // Adding videos - 'thumbnail/video'
    #[Route('/app/admin/other/new', name: 'add_other_video', methods: ['GET', 'POST'])]
    public function addVideo(Request $request, PersistenceManagerRegistry $doctrine, SluggerInterface $slugger, FileUploader $fileUploader): Response
    {
        $otherVideo = new Video();
        $form = $this->createForm(OtherVideoFormType::class, $otherVideo);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $thumbnailFile = $form->get('thumbnail')->getData();
            if ($thumbnailFile) {
                $newFilename = $fileUploader->upload($thumbnailFile);
                $otherVideo->setThumbnail($newFilename);
            }

            $otherVideoFile = $form->get('video')->getData();
            if ($otherVideoFile) {
                $originalFilename = pathinfo($otherVideoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $otherVideoFile->guessExtension();
                try {
                    $otherVideoFile->move(
                        $this->getParameter('video_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $otherVideo->setVideo($newFilename);
            }

            $time = date('d-m-Y');
            $otherVideo->setDate(new \DateTime($time));
            $entityManager = $doctrine->getManager();
            $entityManager->persist($otherVideo);
            $entityManager->flush();

            return $this->redirectToRoute('app_other_video');
        }

        return $this->render('other_video/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Displaying video
    #[Route('/app/admin/other', name: 'app_other_video_index', methods: ['GET', 'POST'])]
    public function showOtherVideo(Request $request, VideoRepository $videoRepository): Response
    {
        $form = $this->createForm(OtherVideoSearchFormType::class);
        $video = $videoRepository->findOtherVideo();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $category = $data->getCategory();
            if($category != ""){
                $video = $videoRepository->findBy(['category' => $category]);
            }
        }
              
        return $this->render('other_video/other_video_dashboard.html.twig', [
            'form' => $form->createView(),
            'otherVideos' => $video,
        ]);
    }

    // Editing a video
    #[Route('/app/admin/other_video/{id}/edit', name: 'app_other_video_edit', methods: ['POST', 'GET'])]
    public function edit(Request $request, Video $video, FileUploader $fileUploader, FileSystem $filesystem, PersistenceManagerRegistry $doctrine): Response 
    {
        $form = $this->createForm(OtherVideoFormType::class, $video);
        $form->remove('video');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thumbnailFile = $form->get('thumbnail')->getData();
            if ($thumbnailFile) {
                $newFilename = $fileUploader->upload($thumbnailFile);
                $filesystem->remove($this->getParameter('thumbnail_directory') . '/' . $video->getThumbnail());
                $video->setThumbnail($newFilename);
            }

            $entityManager = $doctrine->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('app_other_video_index');
        }

        return $this->render('other_video/edit.html.twig', [
            'form' => $form->createView(),
            'video' => $video
        ]);
    }

    // Deleting video 
    #[Route('/app/admin/other_video/{id}/delete', name: 'app_other_video_delete', methods: ['POST'])]
    public function delete(Request $request, Video $video, Filesystem $filesystem, PersistenceManagerRegistry $doctrine): Response
    {
        if ($this->isCsrfTokenValid('delete'.$video->getId(), $request->request->get('_token'))) {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($video);
            $entityManager->flush();

            $videoFile = $video->getVideo();
            $thumbnailFile = $video->getThumbnail();

            // Delete video
            if ($videoFile) {
                $otherVideoFilePath = $this->getParameter('video_directory') . '/' . $videoFile;
                if ($filesystem->exists($otherVideoFilePath)) {
                    $filesystem->remove($otherVideoFilePath);
                }
            }

            // Delete thumbnail
            if ($thumbnailFile) {
                $thumbnailFilePath = $this->getParameter('thumbnail_directory') . '/' . $thumbnailFile;
                if ($filesystem->exists($thumbnailFilePath)) {
                    $filesystem->remove($thumbnailFilePath);
                }
            }
        }

        return $this->redirectToRoute('app_other_video_index');        
    }
}
