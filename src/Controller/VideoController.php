<?php

namespace App\Controller;
//1 - D'abord j'utilise en 'use'
//2 - Après je passe en paramètre de ces méthodes
//3 - Après je peux me servir dans me méthodes
use App\Entity\Video;
use App\Entity\User;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Form\VideoFormType;
use App\Service\FileUploader;
use Symfony\Component\Filesystem\Filesystem;



class VideoController extends AbstractController
{
    #[Route('/app', name: 'dashboard', methods: ['GET'])]
    public function index(VideoRepository $videoRepository): Response
    {
        return $this->render('video/index.html.twig', [
            'videos' => $videoRepository->findAll(),
        ]);
    }

    // Adding videos - 'thumbnail/video'
    #[Route('/app/admin/videos/new', name: 'add_video', methods: ['GET', 'POST'])]
    public function addVideo(Request $request, PersistenceManagerRegistry $doctrine, SluggerInterface $slugger, FileUploader $fileUploader ): Response
    {
        $video = new Video(); //new instance
        $form = $this->createForm(VideoFormType::class, $video); //j'ai appelé le form que vien du VideoFormType
               
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $thumbnailFile = $form->get('thumbnail')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($thumbnailFile) {
                $newFilename = $fileUploader->upload($thumbnailFile);

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                // ça viens du set de ma entity(video.php)
                $video->setThumbnail($newFilename);
            }
            $videoFile = $form->get('video')->getData();
            // Check if a file was uploaded
            if ($videoFile) {
                // Generate a unique filename
                $originalFilename = pathinfo($videoFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$videoFile->guessExtension();
                // Move the file to the destination directory
                try {
                    $videoFile->move(
                        $this->getParameter('video_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                // ça viens du set de ma entity(video.php)
                $video->setVideo($newFilename);

                // Save the file name to the database or do any other necessary action              
                // Logic to handle the case where no file was uploaded
            }
            $entityManager = $doctrine->getManager();
            $entityManager -> persist($video);
            $entityManager -> flush();

            return $this->redirectToRoute('app_video_index');
        }
        return $this->render('video/add.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    // Displaying video
    #[Route('/app/videos/{id}', name: 'app_video_show', methods: ['GET'])]
    public function show(Video $video): Response
    {
        return $this->render('video/show.html.twig', [
            'video' => $video,
        ]);
    }

    #[Route('/app/saved', name: 'saved_videos', methods: ['GET'])]
    public function showSavedVideos(): Response
    {
        $liked = $this->getUser()->getLiked()->toArray();
        return $this->render('video/index.html.twig', [
            'videos' => $liked,
        ]);
    }
    
    #[Route('/app/admin/videos', name: 'app_video_index', methods: ['GET'])]
    public function showAdmVideos(VideoRepository $videoRepository): Response
    {   
        return $this->render('video/video_dashboard.html.twig', [
            'videos' => $videoRepository->findAll(),
        ]);
    }

    // Editing a video
    #[Route('/app/admin/videos/{id}/edit', name: 'app_video_edit', methods: ['POST', 'GET'])]
    public function edit(Request $request, Video $video, VideoRepository $videoRepository, 
    FileUploader $fileUploader, FileSystem $filesystem): Response
    {
    $form = $this->createForm(VideoFormType::class, $video);
    $form->remove('video'); 
    $form->handleRequest($request);

    // Check that the form has been submitted and is valid
    if ($form->isSubmitted() && $form->isValid()) {   
        $thumbnailFile = $form->get('thumbnail')->getData();

        // this condition is needed because the 'brochure' field is not required
        // so the PDF file must be processed only when a file is uploaded
        if ($thumbnailFile) {
            $newFilename = $fileUploader->upload($thumbnailFile);
            $filesystem->remove($this->getParameter('thumbnail_directory').'/'.$video->getThumbnail());

            // updates the 'brochureFilename' property to store the PDF file name
            // instead of its contents
            // ça viens du set de ma entity(video.php)
            $video->setThumbnail($newFilename);
        }   
        // Save changes to the video
        $videoRepository->save($video, true);

        // Redirects to video list page
        return $this->redirectToRoute('app_video_show', ['id' => $video->getId()], Response::HTTP_SEE_OTHER);
    }
        //Render the video editing form
        return $this->render('video/edit.html.twig', [
            'form' => $form->createView(),
            'video' => $video
        ]);
    }   
}

